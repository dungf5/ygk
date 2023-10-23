<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Customize\Service;

use Eccube\Common\EccubeConfig;
use Eccube\Entity\BaseInfo;
use Eccube\Entity\Customer;
use Eccube\Entity\MailHistory;
use Eccube\Entity\MailTemplate;
use Eccube\Entity\OrderItem;
use Eccube\Entity\Shipping;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Repository\BaseInfoRepository;
use Eccube\Repository\MailHistoryRepository;
use Eccube\Repository\MailTemplateRepository;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class MailService
{
    /**
     * @var \Swift_Mailer
     */
    protected $mailer;

    /**
     * @var MailTemplateRepository
     */
    protected $mailTemplateRepository;

    /**
     * @var MailHistoryRepository
     */
    protected $mailHistoryRepository;

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @var BaseInfo
     */
    protected $BaseInfo;

    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * MailService constructor.
     *
     * @param \Swift_Mailer $mailer
     * @param MailTemplateRepository $mailTemplateRepository
     * @param MailHistoryRepository $mailHistoryRepository
     * @param BaseInfoRepository $baseInfoRepository
     * @param EventDispatcherInterface $eventDispatcher
     * @param \Twig_Environment $twig
     * @param EccubeConfig $eccubeConfig
     */
    public function __construct(
        \Swift_Mailer $mailer,
        MailTemplateRepository $mailTemplateRepository,
        MailHistoryRepository $mailHistoryRepository,
        BaseInfoRepository $baseInfoRepository,
        EventDispatcherInterface $eventDispatcher,
        \Twig_Environment $twig,
        EccubeConfig $eccubeConfig
    ) {
        $this->mailer = $mailer;
        $this->mailTemplateRepository = $mailTemplateRepository;
        $this->mailHistoryRepository = $mailHistoryRepository;
        $this->BaseInfo = $baseInfoRepository->get();
        $this->eventDispatcher = $eventDispatcher;
        $this->eccubeConfig = $eccubeConfig;
        $this->twig = $twig;
    }

    /**
     * Send customer confirm mail.
     *
     * @param $Customer 会員情報
     * @param string $activateUrl アクティベート用url
     */
    public function sendCustomerConfirmMail(Customer $Customer, $activateUrl)
    {
        log_info('仮会員登録メール送信開始');

        $MailTemplate = $this->mailTemplateRepository->find($this->eccubeConfig['eccube_entry_confirm_mail_template_id']);

        $body = $this->twig->render($MailTemplate->getFileName(), [
            'Customer' => $Customer,
            'BaseInfo' => $this->BaseInfo,
            'activateUrl' => $activateUrl,
        ]);

        $message = (new \Swift_Message())
            ->setSubject('['.$this->BaseInfo->getShopName().'] '.$MailTemplate->getMailSubject())
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$Customer->getEmail()])
            ->setBcc($this->BaseInfo->getEmail01())
            ->setReplyTo($this->BaseInfo->getEmail03())
            ->setReturnPath($this->BaseInfo->getEmail04());

        // HTMLテンプレートが存在する場合
        $htmlFileName = $this->getHtmlTemplate($MailTemplate->getFileName());
        if (!is_null($htmlFileName)) {
            $htmlBody = $this->twig->render($htmlFileName, [
                'Customer' => $Customer,
                'BaseInfo' => $this->BaseInfo,
                'activateUrl' => $activateUrl,
            ]);

            $message
                ->setContentType('text/plain; charset=UTF-8')
                ->setBody($body, 'text/plain')
                ->addPart($htmlBody, 'text/html');
        } else {
            $message->setBody($body);
        }

        $event = new EventArgs(
            [
                'message' => $message,
                'Customer' => $Customer,
                'BaseInfo' => $this->BaseInfo,
                'activateUrl' => $activateUrl,
            ],
            null
        );
        $this->eventDispatcher->dispatch(EccubeEvents::MAIL_CUSTOMER_CONFIRM, $event);

        $count = $this->mailer->send($message, $failures);

        log_info('仮会員登録メール送信完了', ['count' => $count]);

        return $count;
    }

    /**
     * Send customer complete mail.
     *
     * @param $Customer 会員情報
     */
    public function sendCustomerCompleteMail(Customer $Customer)
    {
        log_info('会員登録完了メール送信開始');

        $MailTemplate = $this->mailTemplateRepository->find($this->eccubeConfig['eccube_entry_complete_mail_template_id']);

        $body = $this->twig->render($MailTemplate->getFileName(), [
            'Customer' => $Customer,
            'BaseInfo' => $this->BaseInfo,
        ]);

        $message = (new \Swift_Message())
            ->setSubject('['.$this->BaseInfo->getShopName().'] '.$MailTemplate->getMailSubject())
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$Customer->getEmail()])
            ->setBcc($this->BaseInfo->getEmail01())
            ->setReplyTo($this->BaseInfo->getEmail03())
            ->setReturnPath($this->BaseInfo->getEmail04());

        // HTMLテンプレートが存在する場合
        $htmlFileName = $this->getHtmlTemplate($MailTemplate->getFileName());
        if (!is_null($htmlFileName)) {
            $htmlBody = $this->twig->render($htmlFileName, [
                'Customer' => $Customer,
                'BaseInfo' => $this->BaseInfo,
            ]);

            $message
                ->setContentType('text/plain; charset=UTF-8')
                ->setBody($body, 'text/plain')
                ->addPart($htmlBody, 'text/html');
        } else {
            $message->setBody($body);
        }

        $event = new EventArgs(
            [
                'message' => $message,
                'Customer' => $Customer,
                'BaseInfo' => $this->BaseInfo,
            ],
            null
        );
        $this->eventDispatcher->dispatch(EccubeEvents::MAIL_CUSTOMER_COMPLETE, $event);

        $count = $this->mailer->send($message);

        log_info('会員登録完了メール送信完了', ['count' => $count]);

        return $count;
    }

    /**
     * Send withdraw mail.
     *
     * @param $Customer Customer
     * @param $email string
     */
    public function sendCustomerWithdrawMail(Customer $Customer, string $email)
    {
        log_info('退会手続き完了メール送信開始');

        $MailTemplate = $this->mailTemplateRepository->find($this->eccubeConfig['eccube_customer_withdraw_mail_template_id']);

        $body = $this->twig->render($MailTemplate->getFileName(), [
            'Customer' => $Customer,
            'BaseInfo' => $this->BaseInfo,
        ]);

        $message = (new \Swift_Message())
            ->setSubject('['.$this->BaseInfo->getShopName().'] '.$MailTemplate->getMailSubject())
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$email])
            ->setBcc($this->BaseInfo->getEmail01())
            ->setReplyTo($this->BaseInfo->getEmail03())
            ->setReturnPath($this->BaseInfo->getEmail04());

        // HTMLテンプレートが存在する場合
        $htmlFileName = $this->getHtmlTemplate($MailTemplate->getFileName());
        if (!is_null($htmlFileName)) {
            $htmlBody = $this->twig->render($htmlFileName, [
                'Customer' => $Customer,
                'BaseInfo' => $this->BaseInfo,
            ]);

            $message
                ->setContentType('text/plain; charset=UTF-8')
                ->setBody($body, 'text/plain')
                ->addPart($htmlBody, 'text/html');
        } else {
            $message->setBody($body);
        }

        $event = new EventArgs(
            [
                'message' => $message,
                'Customer' => $Customer,
                'BaseInfo' => $this->BaseInfo,
                'email' => $email,
            ],
            null
        );
        $this->eventDispatcher->dispatch(EccubeEvents::MAIL_CUSTOMER_WITHDRAW, $event);

        $count = $this->mailer->send($message);

        log_info('退会手続き完了メール送信完了', ['count' => $count]);

        return $count;
    }

    /**
     * Send contact mail.
     *
     * @param $formData お問い合わせ内容
     */
    public function sendContactMail($formData)
    {
        log_info('お問い合わせ受付メール送信開始');

        $MailTemplate = $this->mailTemplateRepository->find($this->eccubeConfig['eccube_contact_mail_template_id']);

        $body = $this->twig->render($MailTemplate->getFileName(), [
            'data' => $formData,
            'BaseInfo' => $this->BaseInfo,
        ]);

        // 問い合わせ者にメール送信
        $message = (new \Swift_Message())
            ->setSubject('['.$this->BaseInfo->getShopName().'] '.$MailTemplate->getMailSubject())
            ->setFrom([$this->BaseInfo->getEmail02() => $this->BaseInfo->getShopName()])
            ->setTo([$formData['email']])
            ->setBcc($this->BaseInfo->getEmail02())
            ->setReplyTo($this->BaseInfo->getEmail02())
            ->setReturnPath($this->BaseInfo->getEmail04());

        // HTMLテンプレートが存在する場合
        $htmlFileName = $this->getHtmlTemplate($MailTemplate->getFileName());
        if (!is_null($htmlFileName)) {
            $htmlBody = $this->twig->render($htmlFileName, [
                'data' => $formData,
                'BaseInfo' => $this->BaseInfo,
            ]);

            $message
                ->setContentType('text/plain; charset=UTF-8')
                ->setBody($body, 'text/plain')
                ->addPart($htmlBody, 'text/html');
        } else {
            $message->setBody($body);
        }

        $event = new EventArgs(
            [
                'message' => $message,
                'formData' => $formData,
                'BaseInfo' => $this->BaseInfo,
            ],
            null
        );
        $this->eventDispatcher->dispatch(EccubeEvents::MAIL_CONTACT, $event);

        $count = $this->mailer->send($message);

        log_info('お問い合わせ受付メール送信完了', ['count' => $count]);

        return $count;
    }

    /**
     * Send order mail.
     *
     * @return \Swift_Message
     */
    public function sendOrderMail($Order, $EC_Order)
    {
        log_info('受注メール送信開始');

        if (empty($Order['email'])) {
            $company = $Order['company_name'] ?? '';
            log_info("Company name: {$company} no email");

            return;
        }

        $MailTemplate = $this->mailTemplateRepository->find($this->eccubeConfig['eccube_order_mail_template_id']);

        $body = $this->twig->render($MailTemplate->getFileName(), [
            'Order' => $Order,
        ]);

        $message = (new \Swift_Message())
            ->setSubject('['.$this->BaseInfo->getShopName().'] '.$MailTemplate->getMailSubject())
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$Order['email']])
            ->setBcc($this->BaseInfo->getEmail01())
            ->setReplyTo($this->BaseInfo->getEmail03())
            ->setReturnPath($this->BaseInfo->getEmail04());

        // Set CC mail super user if any
        if (!empty($Order['emailcc'])) {
            $message->setCc($Order['emailcc']);
        }

        // HTMLテンプレートが存在する場合
        $htmlFileName = $this->getHtmlTemplate($MailTemplate->getFileName());
        if (!is_null($htmlFileName)) {
            $htmlBody = $this->twig->render($htmlFileName, [
                'Order' => $Order,
            ]);

            $message
                ->setContentType('text/plain; charset=UTF-8')
                ->setBody($body, 'text/plain')
                ->addPart($htmlBody, 'text/html');
        } else {
            $message->setBody($body);
        }

        $event = new EventArgs(
            [
                'message' => $message,
                'Order' => $Order,
                'MailTemplate' => $MailTemplate,
                'BaseInfo' => $this->BaseInfo,
            ],
            null
        );
        $this->eventDispatcher->dispatch(EccubeEvents::MAIL_ORDER, $event);

        $count = $this->mailer->send($message);

        $MailHistory = new MailHistory();
        $MailHistory->setMailSubject($message->getSubject())
            ->setMailBody($message->getBody())
            ->setOrder($EC_Order)
            ->setSendDate(new \DateTime());

        // HTML用メールの設定
        $multipart = $message->getChildren();
        if (count($multipart) > 0) {
            $MailHistory->setMailHtmlBody($multipart[0]->getBody());
        }

        $this->mailHistoryRepository->save($MailHistory);

        log_info('受注メール送信完了', ['count' => $count]);

        return $message;
    }

    /**
     * Send admin customer confirm mail.
     *
     * @param $Customer 会員情報
     * @param string $activateUrl アクティベート用url
     */
    public function sendAdminCustomerConfirmMail(Customer $Customer, $activateUrl)
    {
        log_info('仮会員登録再送メール送信開始');

        /* @var $MailTemplate \Eccube\Entity\MailTemplate */
        $MailTemplate = $this->mailTemplateRepository->find($this->eccubeConfig['eccube_entry_confirm_mail_template_id']);

        $body = $this->twig->render($MailTemplate->getFileName(), [
            'BaseInfo' => $this->BaseInfo,
            'Customer' => $Customer,
            'activateUrl' => $activateUrl,
        ]);

        $message = (new \Swift_Message())
            ->setSubject('['.$this->BaseInfo->getShopName().'] '.$MailTemplate->getMailSubject())
            ->setFrom([$this->BaseInfo->getEmail03() => $this->BaseInfo->getShopName()])
            ->setTo([$Customer->getEmail()])
            ->setBcc($this->BaseInfo->getEmail01())
            ->setReplyTo($this->BaseInfo->getEmail03())
            ->setReturnPath($this->BaseInfo->getEmail04());

        // HTMLテンプレートが存在する場合
        $htmlFileName = $this->getHtmlTemplate($MailTemplate->getFileName());
        if (!is_null($htmlFileName)) {
            $htmlBody = $this->twig->render($htmlFileName, [
                'BaseInfo' => $this->BaseInfo,
                'Customer' => $Customer,
                'activateUrl' => $activateUrl,
            ]);

            $message
                ->setContentType('text/plain; charset=UTF-8')
                ->setBody($body, 'text/plain')
                ->addPart($htmlBody, 'text/html');
        } else {
            $message->setBody($body);
        }

        $event = new EventArgs(
            [
                'message' => $message,
                'Customer' => $Customer,
                'BaseInfo' => $this->BaseInfo,
                'activateUrl' => $activateUrl,
            ],
            null
        );
        $this->eventDispatcher->dispatch(EccubeEvents::MAIL_ADMIN_CUSTOMER_CONFIRM, $event);

        $count = $this->mailer->send($message);

        log_info('仮会員登録再送メール送信完了', ['count' => $count]);

        return $count;
    }

    /**
     * Send admin order mail.
     *
     * @param Order $Order 受注情報
     * @param $formData 入力内容
     *
     * @return \Swift_Message
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function sendAdminOrderMail(Order $Order, $formData)
    {
        log_info('受注管理通知メール送信開始');

        $message = (new \Swift_Message())
            ->setSubject('['.$this->BaseInfo->getShopName().'] '.$formData['mail_subject'])
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$Order->getEmail()])
            ->setBcc($this->BaseInfo->getEmail01())
            ->setReplyTo($this->BaseInfo->getEmail03())
            ->setReturnPath($this->BaseInfo->getEmail04())
            ->setBody($formData['tpl_data']);

        $event = new EventArgs(
            [
                'message' => $message,
                'Order' => $Order,
                'formData' => $formData,
                'BaseInfo' => $this->BaseInfo,
            ],
            null
        );
        $this->eventDispatcher->dispatch(EccubeEvents::MAIL_ADMIN_ORDER, $event);

        $count = $this->mailer->send($message);

        log_info('受注管理通知メール送信完了', ['count' => $count]);

        return $message;
    }

    /**
     * Send password reset notification mail.
     *
     * @param $Customer 会員情報
     * @param string $reset_url
     */
    public function sendPasswordResetNotificationMail(Customer $Customer, $reset_url)
    {
        log_info('パスワード再発行メール送信開始');

        $MailTemplate = $this->mailTemplateRepository->find($this->eccubeConfig['eccube_forgot_mail_template_id']);
        $body = $this->twig->render($MailTemplate->getFileName(), [
            'BaseInfo' => $this->BaseInfo,
            'Customer' => $Customer,
            'expire' => $this->eccubeConfig['eccube_customer_reset_expire'],
            'reset_url' => $reset_url,
        ]);

        $message = (new \Swift_Message())
            ->setSubject('['.$this->BaseInfo->getShopName().'] '.$MailTemplate->getMailSubject())
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$Customer->getEmail()])
            ->setReplyTo($this->BaseInfo->getEmail03())
            ->setReturnPath($this->BaseInfo->getEmail04());

        // HTMLテンプレートが存在する場合
        $htmlFileName = $this->getHtmlTemplate($MailTemplate->getFileName());
        if (!is_null($htmlFileName)) {
            $htmlBody = $this->twig->render($htmlFileName, [
                'BaseInfo' => $this->BaseInfo,
                'Customer' => $Customer,
                'expire' => $this->eccubeConfig['eccube_customer_reset_expire'],
                'reset_url' => $reset_url,
            ]);

            $message
                ->setContentType('text/plain; charset=UTF-8')
                ->setBody($body, 'text/plain')
                ->addPart($htmlBody, 'text/html');
        } else {
            $message->setBody($body);
        }

        $event = new EventArgs(
            [
                'message' => $message,
                'Customer' => $Customer,
                'BaseInfo' => $this->BaseInfo,
                'resetUrl' => $reset_url,
            ],
            null
        );
        $this->eventDispatcher->dispatch(EccubeEvents::MAIL_PASSWORD_RESET, $event);

        $count = $this->mailer->send($message);

        log_info('パスワード再発行メール送信完了', ['count' => $count]);

        return $count;
    }

    /**
     * Send password reset notification mail.
     *
     * @param $Customer 会員情報
     * @param string $password
     */
    public function sendPasswordResetCompleteMail(Customer $Customer, $password)
    {
        log_info('パスワード変更完了メール送信開始');

        $MailTemplate = $this->mailTemplateRepository->find($this->eccubeConfig['eccube_reset_complete_mail_template_id']);

        $body = $this->twig->render($MailTemplate->getFileName(), [
            'BaseInfo' => $this->BaseInfo,
            'Customer' => $Customer,
            'password' => $password,
        ]);

        $message = (new \Swift_Message())
            ->setSubject('['.$this->BaseInfo->getShopName().'] '.$MailTemplate->getMailSubject())
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$Customer->getEmail()])
            ->setBcc($this->BaseInfo->getEmail01())
            ->setReplyTo($this->BaseInfo->getEmail03())
            ->setReturnPath($this->BaseInfo->getEmail04());

        // HTMLテンプレートが存在する場合
        $htmlFileName = $this->getHtmlTemplate($MailTemplate->getFileName());
        if (!is_null($htmlFileName)) {
            $htmlBody = $this->twig->render($htmlFileName, [
                'BaseInfo' => $this->BaseInfo,
                'Customer' => $Customer,
                'password' => $password,
            ]);

            $message
                ->setContentType('text/plain; charset=UTF-8')
                ->setBody($body, 'text/plain')
                ->addPart($htmlBody, 'text/html');
        } else {
            $message->setBody($body);
        }

        $event = new EventArgs(
            [
                'message' => $message,
                'Customer' => $Customer,
                'BaseInfo' => $this->BaseInfo,
                'password' => $password,
            ],
            null
        );
        $this->eventDispatcher->dispatch(EccubeEvents::MAIL_PASSWORD_RESET_COMPLETE, $event);

        $count = $this->mailer->send($message);

        log_info('パスワード変更完了メール送信完了', ['count' => $count]);

        return $count;
    }

    /**
     * 発送通知メールを送信する.
     * 発送通知メールは受注ごとに送られる
     *
     * @param Shipping $Shipping
     *
     * @throws \Twig_Error
     */
    public function sendShippingNotifyMail(Shipping $Shipping)
    {
        log_info('出荷通知メール送信処理開始', ['id' => $Shipping->getId()]);

        $MailTemplate = $this->mailTemplateRepository->find($this->eccubeConfig['eccube_shipping_notify_mail_template_id']);

        /** @var Order $Order */
        $Order = $Shipping->getOrder();
        $body = $this->getShippingNotifyMailBody($Shipping, $Order, $MailTemplate->getFileName());

        $message = (new \Swift_Message())
            ->setSubject('['.$this->BaseInfo->getShopName().'] '.$MailTemplate->getMailSubject())
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo($Order->getEmail())
            ->setBcc($this->BaseInfo->getEmail01())
            ->setReplyTo($this->BaseInfo->getEmail03())
            ->setReturnPath($this->BaseInfo->getEmail04());

        // HTMLテンプレートが存在する場合
        $htmlFileName = $this->getHtmlTemplate($MailTemplate->getFileName());
        if (!is_null($htmlFileName)) {
            $htmlBody = $this->getShippingNotifyMailBody($Shipping, $Order, $htmlFileName, true);

            $message
                ->setContentType('text/plain; charset=UTF-8')
                ->setBody($body, 'text/plain')
                ->addPart($htmlBody, 'text/html');
        } else {
            $message->setBody($body);
        }

        $this->mailer->send($message);

        $MailHistory = new MailHistory();
        $MailHistory->setMailSubject($message->getSubject())
            ->setMailBody($message->getBody())
            ->setOrder($Order)
            ->setSendDate(new \DateTime());

        // HTML用メールの設定
        $multipart = $message->getChildren();
        if (count($multipart) > 0) {
            $MailHistory->setMailHtmlBody($multipart[0]->getBody());
        }

        $this->mailHistoryRepository->save($MailHistory);

        log_info('出荷通知メール送信処理完了', ['id' => $Shipping->getId()]);
    }

    /**
     * @param Shipping $Shipping
     * @param Order $Order
     * @param string|null $templateName
     * @param boolean $is_html
     *
     * @return string
     *
     * @throws \Twig_Error
     */
    public function getShippingNotifyMailBody(Shipping $Shipping, Order $Order, $templateName = null, $is_html = false)
    {
        $ShippingItems = array_filter($Shipping->getOrderItems()->toArray(), function (OrderItem $OrderItem) use ($Order) {
            return $OrderItem->getOrderId() === $Order->getId();
        });

        if (is_null($templateName)) {
            /** @var MailTemplate $MailTemplate */
            $MailTemplate = $this->mailTemplateRepository->find($this->eccubeConfig['eccube_shipping_notify_mail_template_id']);
            $fileName = $MailTemplate->getFileName();
        } else {
            $fileName = $templateName;
        }

        if ($is_html) {
            $htmlFileName = $this->getHtmlTemplate($fileName);
            $fileName = !is_null($htmlFileName) ? $htmlFileName : $fileName;
        }

        return $this->twig->render($fileName, [
            'Shipping' => $Shipping,
            'ShippingItems' => $ShippingItems,
            'Order' => $Order,
        ]);
    }

    /**
     * [getHtmlTemplate description]
     *
     * @param  string $templateName  プレーンテキストメールのファイル名
     *
     * @return string|null  存在する場合はファイル名を返す
     */
    public function getHtmlTemplate($templateName)
    {
        // メールテンプレート名からHTMLメール用テンプレート名を生成
        $fileName = explode('.', $templateName);
        $suffix = '.html';
        $htmlFileName = $fileName[0].$suffix.'.'.$fileName[1];

        // HTMLメール用テンプレートの存在チェック
        if ($this->twig->getLoader()->exists($htmlFileName)) {
            return $htmlFileName;
        } else {
            return null;
        }
    }

    /**
     * Send mail import order ws-eos.
     *
     * @param array $information
     *
     * @return \Swift_Message
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function sendMailImportWSEOS($information = [])
    {
        if (empty($information)) {
            return;
        }

        if (empty($information['email'])) {
            return;
        }

        log_info('[WS-EOS] Start Send Mail FTP');

        // Information successfully
        if ($information['status'] == 1) {
            $information['subject_mail'] = 'EOS注文データ受信が完了しました';
            $information['title_mail'] = '※本メールは自動配信メールです。';
            $information['title_time'] = '受信完了日時';
            $information['content1'] = '※大変お手数ではございますがお問い合わせは弊社営業担当者まで';
            $information['content2'] = '　ご連絡くださいますようお願いいたします。';
        }

        // Information error
        if ($information['status'] == 0) {
            $information['subject_mail'] = 'EOS注文データ受信にエラーが発生しました';
            $information['title_mail'] = '※本メールは自動配信メールです。';
            $information['content1'] = 'エラー内容は以下となります。ご確認をお願いいたします。';
            $information['content2'] = '※大変お手数ではございますがお問い合わせは弊社営業担当者まで';
            $information['content3'] = '　ご連絡くださいますようお願いいたします。';
            $information['error_title'] = 'エラー内容';
        }

        $body = $this->twig->render($information['file_name'], [
            'information' => $information,
        ]);

        $message = (new \Swift_Message())
            ->setSubject('['.$this->BaseInfo->getShopName().'] '.$information['subject_mail'])
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$information['email']])
            ->setReplyTo($this->BaseInfo->getEmail03())
            ->setReturnPath($this->BaseInfo->getEmail04());

        if (!empty($information['email_cc'])) {
            $message->setCc($information['email_cc']);
        }

        if (!empty($information['email_bcc'])) {
            $message->setBcc($information['email_bcc']);
        }

        // HTMLテンプレートが存在する場合
        $htmlFileName = $this->getHtmlTemplate($information['file_name']);
        if (!is_null($htmlFileName)) {
            $htmlBody = $this->twig->render($htmlFileName, [
                'information' => $information,
            ]);

            $message
                ->setContentType('text/plain; charset=UTF-8')
                ->setBody($body, 'text/plain')
                ->addPart($htmlBody, 'text/html');
        } else {
            $message->setBody($body);
        }

        $count = $this->mailer->send($message);
        log_info('[WS-EOS] End Send Mail FTP', ['count' => $count]);

        return $message;
    }

    /**
     * Send mail import order ws-eos.
     *
     * @param array $information
     *
     * @return \Swift_Message
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function sendMailImportNatEOS($information = [])
    {
        if (empty($information)) {
            return;
        }

        if (empty($information['email'])) {
            return;
        }

        log_info('[NAT-EOS] Start Send Mail FTP');

        // Information successfully
        if ($information['status'] == 1) {
            $information['subject_mail'] = '発注データ受信が完了しました';
            $information['title_mail'] = '※本メールは自動配信メールです。';
            $information['title_time'] = '受信完了日時';
            $information['content1'] = '※大変お手数ではございますがお問い合わせは弊社営業担当者まで';
            $information['content2'] = '　ご連絡くださいますようお願いいたします。';
        }

        // Information error
        if ($information['status'] == 0) {
            $information['subject_mail'] = '発注データ受信にエラーが発生しました';
            $information['title_mail'] = '※本メールは自動配信メールです。';
            $information['content1'] = 'エラー内容は以下となります。ご確認をお願いいたします。';
            $information['content2'] = '※大変お手数ではございますがお問い合わせは弊社営業担当者まで';
            $information['content3'] = '　ご連絡くださいますようお願いいたします。';
            $information['error_title'] = 'エラー内容';
        }

        $body = $this->twig->render($information['file_name'], [
            'information' => $information,
        ]);

        $message = (new \Swift_Message())
            ->setSubject('['.$this->BaseInfo->getShopName().'] '.$information['subject_mail'])
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$information['email']])
            ->setReplyTo($this->BaseInfo->getEmail03())
            ->setReturnPath($this->BaseInfo->getEmail04());

        if (!empty($information['email_cc'])) {
            $message->setCc($information['email_cc']);
        }

        if (!empty($information['email_bcc'])) {
            $message->setBcc($information['email_bcc']);
        }

        // HTMLテンプレートが存在する場合
        $htmlFileName = $this->getHtmlTemplate($information['file_name']);
        if (!is_null($htmlFileName)) {
            $htmlBody = $this->twig->render($htmlFileName, [
                'information' => $information,
            ]);

            $message
                ->setContentType('text/plain; charset=UTF-8')
                ->setBody($body, 'text/plain')
                ->addPart($htmlBody, 'text/html');
        } else {
            $message->setBody($body);
        }

        $count = $this->mailer->send($message);
        log_info('[NAT-EOS] End Send Mail FTP', ['count' => $count]);

        return $message;
    }

    /**
     * Send error ws eos mail.
     *
     * @param string $template
     * @param array $information
     *
     * @return \Swift_Message
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function sendMailErrorWSEOS($information = [])
    {
        if (empty($information)) {
            return;
        }

        if (empty($information['email'])) {
            return;
        }

        log_info('[WS-EOS] Start Send Mail Validate Error.');

        // Information
        $information['subject_mail'] = 'EOS注文データにエラーがありました';
        $information['title_mail'] = '※本メールは自動配信メールです。';
        $information['error_title'] = 'エラー内容は以下となります。ご確認をお願いいたします。';
        $information['content'] = '※大変お手数ではございますがお問い合わせは弊社営業担当者まで';
        $information['content2'] = '　ご連絡くださいますようお願いいたします。';

        $body = $this->twig->render($information['file_name'], [
            'information' => $information,
        ]);

        $message = (new \Swift_Message())
            ->setSubject('['.$this->BaseInfo->getShopName().'] '.$information['subject_mail'])
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$information['email']])
            ->setReplyTo($this->BaseInfo->getEmail03())
            ->setReturnPath($this->BaseInfo->getEmail04());

        if (!empty($information['email_cc'])) {
            $message->setCc($information['email_cc']);
        }

        if (!empty($information['email_bcc'])) {
            $message->setBcc($information['email_bcc']);
        }

        // HTMLテンプレートが存在する場合
        $htmlFileName = $this->getHtmlTemplate($information['file_name']);
        if (!is_null($htmlFileName)) {
            $htmlBody = $this->twig->render($htmlFileName, [
                'information' => $information,
            ]);

            $message
                ->setContentType('text/plain; charset=UTF-8')
                ->setBody($body, 'text/plain')
                ->addPart($htmlBody, 'text/html');
        } else {
            $message->setBody($body);
        }

        $count = $this->mailer->send($message);
        log_info('[WS-EOS] End Send Mail Validate Error.', ['count' => $count]);

        return $message;
    }

    /**
     * Send error nat eos mail.
     *
     * @param string $template
     * @param array $information
     *
     * @return \Swift_Message
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function sendMailErrorNatEOS($information = [])
    {
        if (empty($information)) {
            return;
        }

        if (empty($information['email'])) {
            return;
        }

        log_info('[NAT-EOS] Start Send Mail Validate Error.');

        // Information
        $information['subject_mail'] = '発注データにエラーがありました';
        $information['title_mail'] = '※本メールは自動配信メールです。';
        $information['error_title'] = 'エラー内容は以下となります。ご確認をお願いいたします。';
        $information['content'] = '※大変お手数ではございますがお問い合わせは弊社営業担当者まで';
        $information['content2'] = '　ご連絡くださいますようお願いいたします。';

        $body = $this->twig->render($information['file_name'], [
            'information' => $information,
        ]);

        $message = (new \Swift_Message())
            ->setSubject('['.$this->BaseInfo->getShopName().'] '.$information['subject_mail'])
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$information['email']])
            ->setReplyTo($this->BaseInfo->getEmail03())
            ->setReturnPath($this->BaseInfo->getEmail04());

        if (!empty($information['email_cc'])) {
            $message->setCc($information['email_cc']);
        }

        if (!empty($information['email_bcc'])) {
            $message->setBcc($information['email_bcc']);
        }

        // HTMLテンプレートが存在する場合
        $htmlFileName = $this->getHtmlTemplate($information['file_name']);
        if (!is_null($htmlFileName)) {
            $htmlBody = $this->twig->render($htmlFileName, [
                'information' => $information,
            ]);

            $message
                ->setContentType('text/plain; charset=UTF-8')
                ->setBody($body, 'text/plain')
                ->addPart($htmlBody, 'text/html');
        } else {
            $message->setBody($body);
        }

        $count = $this->mailer->send($message);
        log_info('[NAT-EOS] End Send Mail Validate Error.', ['count' => $count]);

        return $message;
    }

    /**
     * Send order success ws eos mail.
     *
     * @param string $template
     * @param array $information
     *
     * @return \Swift_Message
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function sendMailOrderSuccessEOS($information = [])
    {
        if (empty($information)) {
            return;
        }

        if (empty($information['email'])) {
            return;
        }

        log_info('[EOS] Start Send Mail Order Success.');

        // Information
        $information['subject_mail'] = 'ご注文ありがとうございます';
        $information['title_mail'] = 'この度はご注文いただき誠にありがとうございます。';
        $information['content'] = '下記ご注文内容にお間違えがないかご確認下さい。';
        $information['content2'] = 'お問い合わせは弊社営業担当者までご連絡くださいますようお願いいたします。';

        $body = $this->twig->render($information['file_name'], [
            'information' => $information,
        ]);

        $message = (new \Swift_Message())
            ->setSubject('['.$this->BaseInfo->getShopName().'] '.$information['subject_mail'])
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$information['email']])
            ->setReplyTo($this->BaseInfo->getEmail03())
            ->setReturnPath($this->BaseInfo->getEmail04());

        if (!empty($information['email_cc'])) {
            $message->setCc($information['email_cc']);
        }

        if (!empty($information['email_bcc'])) {
            $message->setBcc($information['email_bcc']);
        }

        // HTMLテンプレートが存在する場合
        $htmlFileName = $this->getHtmlTemplate($information['file_name']);
        if (!is_null($htmlFileName)) {
            $htmlBody = $this->twig->render($htmlFileName, [
                'information' => $information,
            ]);

            $message
                ->setContentType('text/plain; charset=UTF-8')
                ->setBody($body, 'text/plain')
                ->addPart($htmlBody, 'text/html');
        } else {
            $message->setBody($body);
        }

        $count = $this->mailer->send($message);
        log_info('[EOS] End Send Mail Order Success.', ['count' => $count]);

        return $message;
    }

    public function sendMailReturnProductPreview($email, $url_preview)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $body = $this->twig->render('Mail/return_product_preview.twig', [
            'url_preview' => $url_preview,
        ]);

        $message = (new \Swift_Message())
            ->setSubject('[XBRAID JAPAN] 返品リクエストが届きました')
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$email])
            ->setBody($body);

        if (getenv('APP_IS_LOCAL') != 1 && getenv('EMAIL_BCC')) {
            $email = getenv('EMAIL_BCC');
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message->setBcc($email);
            }
        }

        return $this->mailer->send($message);
    }

    public function sendMailReturnProductApprove($email, $data)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $body = $this->twig->render('Mail/return_product_approve.twig', [
            'data' => $data,
        ]);

        $message = (new \Swift_Message())
            ->setSubject('[XBRAID JAPAN]  返品リクエストが届きました')
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$email])
            ->setBody($body);

        if (getenv('APP_IS_LOCAL') != 1 && getenv('EMAIL_BCC')) {
            $email = getenv('EMAIL_BCC');
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message->setBcc($email);
            }
        }

        return $this->mailer->send($message);
    }

    public function sendMailReturnProductApproveYes($email, $data)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $body = $this->twig->render('Mail/return_product_approve_yes.twig', [
            'data' => $data,
        ]);
        $message = (new \Swift_Message())
            ->setSubject('[XBRAID JAPAN] 返品リクエスト承認のご案内')
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$email])
            ->setBody($body);

        if (getenv('APP_IS_LOCAL') != 1 && getenv('EMAIL_BCC')) {
            $email = getenv('EMAIL_BCC');
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message->setBcc($email);
            }
        }

        return $this->mailer->send($message);
    }

    public function sendMailReturnProductApproveNo($email, $aprove_comment_not_yet)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $body = $this->twig->render('Mail/return_product_approve_no.twig', [
            'aprove_comment_not_yet' => $aprove_comment_not_yet,
        ]);
        $message = (new \Swift_Message())
            ->setSubject('[XBRAID JAPAN] 返品リクエスト未承認のお知らせ')
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$email])
            ->setBody($body);

        if (getenv('APP_IS_LOCAL') != 1 && getenv('EMAIL_BCC')) {
            $email = getenv('EMAIL_BCC');
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message->setBcc($email);
            }
        }

        return $this->mailer->send($message);
    }

    public function sendMailReturnProductReceiptYes($email, $data)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $body = $this->twig->render('Mail/return_product_receipt_yes.twig', [
            'data' => $data,
        ]);
        $message = (new \Swift_Message())
            ->setSubject('[XBRAID JAPAN] 返品商品受取受理のご案内')
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$email])
            ->setBody($body);

        if (getenv('APP_IS_LOCAL') != 1 && getenv('EMAIL_BCC')) {
            $email = getenv('EMAIL_BCC');
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message->setBcc($email);
            }
        }

        return $this->mailer->send($message);
    }

    public function sendMailReturnProductReceiptNo($email, $receipt_not_yet_comment)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $body = $this->twig->render('Mail/return_product_receipt_no.twig', [
            'receipt_not_yet_comment' => $receipt_not_yet_comment,
        ]);
        $message = (new \Swift_Message())
            ->setSubject('[XBRAID JAPAN] 返品商品受取未受理のお知らせ')
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$email])
            ->setBody($body);

        if (getenv('APP_IS_LOCAL') != 1 && getenv('EMAIL_BCC')) {
            $email = getenv('EMAIL_BCC');
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message->setBcc($email);
            }
        }

        return $this->mailer->send($message);
    }

    public function sendMailReturnProductComplete($email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $body = $this->twig->render('Mail/return_product_complete.twig');
        $message = (new \Swift_Message())
            ->setSubject('[XBRAID JAPAN] 返品完了のお知らせ')
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$email])
            ->setBody($body);

        if (getenv('APP_IS_LOCAL') != 1 && getenv('EMAIL_BCC')) {
            $email = getenv('EMAIL_BCC');
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message->setBcc($email);
            }
        }

        return $this->mailer->send($message);
    }

    /**
     * Send mail export order ws-eos.
     *
     * @param array $information
     *
     * @return \Swift_Message
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function sendMailExportWSEOS($information = [])
    {
        if (empty($information)) {
            return;
        }

        if (empty($information['email'])) {
            return;
        }

        log_info('[WS-EOS] Start Send Mail FTP');

        // Information successfully
        if ($information['status'] == 1) {
            $information['subject_mail'] = 'EOS出荷データの送信が完了しました';
            $information['title_mail'] = '※本メールは自動配信メールです。';
            $information['title_time'] = '送信完了日時';
            $information['content1'] = '※大変お手数ではございますがお問い合わせは弊社営業担当者まで';
            $information['content2'] = '　ご連絡くださいますようお願いいたします。';
        }

        // Information error
        if ($information['status'] == 0) {
            $information['subject_mail'] = 'EOS出荷データ送信にエラーが発生しました';
            $information['title_mail'] = '※本メールは自動配信メールです。';
            $information['content1'] = 'エラー内容は以下となります。ご確認をお願いいたします。';
            $information['content2'] = '※大変お手数ではございますがお問い合わせは弊社営業担当者まで';
            $information['content3'] = '　ご連絡くださいますようお願いいたします。';
            $information['error_title'] = 'エラー内容';
        }

        $body = $this->twig->render($information['file_name'], [
            'information' => $information,
        ]);

        $message = (new \Swift_Message())
            ->setSubject('['.$this->BaseInfo->getShopName().'] '.$information['subject_mail'])
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$information['email']])
            ->setReplyTo($this->BaseInfo->getEmail03())
            ->setReturnPath($this->BaseInfo->getEmail04());

        if (!empty($information['email_cc'])) {
            $message->setCc($information['email_cc']);
        }

        if (!empty($information['email_bcc'])) {
            $message->setBcc($information['email_bcc']);
        }

        // HTMLテンプレートが存在する場合
        $htmlFileName = $this->getHtmlTemplate($information['file_name']);
        if (!is_null($htmlFileName)) {
            $htmlBody = $this->twig->render($htmlFileName, [
                'information' => $information,
            ]);

            $message
                ->setContentType('text/plain; charset=UTF-8')
                ->setBody($body, 'text/plain')
                ->addPart($htmlBody, 'text/html');
        } else {
            $message->setBody($body);
        }

        $count = $this->mailer->send($message);
        log_info('[WS-EOS] End Send Mail FTP', ['count' => $count]);

        return $message;
    }

    /**
     * Send mail export nat stock list.
     *
     * @param array $information
     *
     * @return \Swift_Message
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function sendMailExportNatStock($information = [])
    {
        if (empty($information)) {
            return;
        }

        if (empty($information['email'])) {
            return;
        }

        log_info('[WS-EOS] Start Send Mail FTP');

        // Information successfully
        if ($information['status'] == 1) {
            $information['subject_mail'] = '在庫データの送信が完了しました';
            $information['title_mail'] = '※本メールは自動配信メールです。';
            $information['title_time'] = '送信完了日時';
            $information['content1'] = '※大変お手数ではございますがお問い合わせは弊社営業担当者まで';
            $information['content2'] = '　ご連絡くださいますようお願いいたします。';
        }

        // Information error
        if ($information['status'] == 0) {
            $information['subject_mail'] = '在庫データ送信にエラーが発生しました';
            $information['title_mail'] = '※本メールは自動配信メールです。';
            $information['content1'] = 'エラー内容は以下となります。ご確認をお願いいたします。';
            $information['content2'] = '※大変お手数ではございますがお問い合わせは弊社営業担当者まで';
            $information['content3'] = '　ご連絡くださいますようお願いいたします。';
            $information['error_title'] = 'エラー内容';
        }

        $body = $this->twig->render($information['file_name'], [
            'information' => $information,
        ]);

        $message = (new \Swift_Message())
            ->setSubject('['.$this->BaseInfo->getShopName().'] '.$information['subject_mail'])
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$information['email']])
            ->setReplyTo($this->BaseInfo->getEmail03())
            ->setReturnPath($this->BaseInfo->getEmail04());

        if (!empty($information['email_cc'])) {
            $message->setCc($information['email_cc']);
        }

        if (!empty($information['email_bcc'])) {
            $message->setBcc($information['email_bcc']);
        }

        // HTMLテンプレートが存在する場合
        $htmlFileName = $this->getHtmlTemplate($information['file_name']);
        if (!is_null($htmlFileName)) {
            $htmlBody = $this->twig->render($htmlFileName, [
                'information' => $information,
            ]);

            $message
                ->setContentType('text/plain; charset=UTF-8')
                ->setBody($body, 'text/plain')
                ->addPart($htmlBody, 'text/html');
        } else {
            $message->setBody($body);
        }

        $count = $this->mailer->send($message);
        log_info('[WS-EOS] End Send Mail FTP', ['count' => $count]);

        return $message;
    }

    /**
     * Send mail export nat eos data.
     *
     * @param array $information
     *
     * @return \Swift_Message
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function sendMailExportNatEOS($information = [])
    {
        if (empty($information)) {
            return;
        }

        if (empty($information['email'])) {
            return;
        }

        log_info('[NAT-EOS] Start Send Mail FTP');

        // Information successfully
        if ($information['status'] == 1) {
            $information['subject_mail'] = 'EOS納品データの送信が完了しました';
            $information['title_mail'] = '※本メールは自動配信メールです。';
            $information['title_time'] = '送信完了日時';
            $information['content1'] = '※大変お手数ではございますがお問い合わせは弊社営業担当者まで';
            $information['content2'] = '　ご連絡くださいますようお願いいたします。';
        }

        // Information error
        if ($information['status'] == 0) {
            $information['subject_mail'] = 'EOS納品データ送信にエラーが発生しました';
            $information['title_mail'] = '※本メールは自動配信メールです。';
            $information['content1'] = 'エラー内容は以下となります。ご確認をお願いいたします。';
            $information['content2'] = '※大変お手数ではございますがお問い合わせは弊社営業担当者まで';
            $information['content3'] = '　ご連絡くださいますようお願いいたします。';
            $information['error_title'] = 'エラー内容';
        }

        $body = $this->twig->render($information['file_name'], [
            'information' => $information,
        ]);

        $message = (new \Swift_Message())
            ->setSubject('['.$this->BaseInfo->getShopName().'] '.$information['subject_mail'])
            ->setFrom([$this->BaseInfo->getEmail01() => $this->BaseInfo->getShopName()])
            ->setTo([$information['email']])
            ->setReplyTo($this->BaseInfo->getEmail03())
            ->setReturnPath($this->BaseInfo->getEmail04());

        if (!empty($information['email_cc'])) {
            $message->setCc($information['email_cc']);
        }

        if (!empty($information['email_bcc'])) {
            $message->setBcc($information['email_bcc']);
        }

        // HTMLテンプレートが存在する場合
        $htmlFileName = $this->getHtmlTemplate($information['file_name']);
        if (!is_null($htmlFileName)) {
            $htmlBody = $this->twig->render($htmlFileName, [
                'information' => $information,
            ]);

            $message
                ->setContentType('text/plain; charset=UTF-8')
                ->setBody($body, 'text/plain')
                ->addPart($htmlBody, 'text/html');
        } else {
            $message->setBody($body);
        }

        $count = $this->mailer->send($message);
        log_info('[NAT-EOS] End Send Mail FTP', ['count' => $count]);

        return $message;
    }
}
