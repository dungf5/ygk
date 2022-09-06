-- --------------------------------------------------------
-- ホスト:                          localhost
-- サーバーのバージョン:                   8.0.28 - Source distribution
-- サーバー OS:                      Linux
-- HeidiSQL バージョン:               12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--  プロシージャ ec_tmp.update_mst_customer の構造をダンプしています
DELIMITER //
CREATE PROCEDURE `update_mst_customer`()
BEGIN

-- 例外とエラー変数 
	DECLARE sp_name CHAR(100) DEFAULT 'update_mst_customer';
	DECLARE process_step_name CHAR(100) DEFAULT 'Start_process';
	DECLARE code CHAR(5) DEFAULT '00000';
	DECLARE msg TEXT DEFAULT 'Finished';
	DECLARE rows_count INT DEFAULT 0;
	DECLARE ope_name CHAR(50);
	DECLARE done INT;
					
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
-- Declare exception handler for failed insert
	DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		GET DIAGNOSTICS CONDITION 1
		code = RETURNED_SQLSTATE, msg = MESSAGE_TEXT;
		ROLLBACK;
		CALL ec_tmp.write_logs(sp_name, process_step_name, code, msg, rows_count, ope_name);
	END;
-- 実行者名を取得する
	SET ope_name = SESSION_USER();

#=============================1.insert ec dtb_customer
	replace ygk_real.dtb_customer(customer_code,customer_status_id,name01,name02,company_name,postal_code,addr01,addr02,password,secret_key,email,discriminator_type,create_date,update_date)
	select a.customer_code,2,ifnull(a.customer_name,a.company_name),ifnull(a.customer_name,'テスト1')
	,a.company_name,a.postal_code,a.addr01,concat(a.addr02,a.addr03),'ed981df30d4d499df317e9716ff9392714a8dac849a0f59fe07e8bcdc22a0daa'
	,a.customer_code,ifnull(a.email,'***'),'customer',a.create_date,a.update_date
	from ygk_real.mst_customer as a
	left join ygk_real.dtb_customer as b
	on a.customer_code = b.customer_code 
	
	where b.customer_code is  null;
	
#=============================4.update 紐づけmst_customer
	update
	ygk_real.mst_customer as a
	join ygk_real.dtb_customer as b
	on a.customer_code = b.customer_code
	set a.ec_customer_id = b.id,a.email = b.email
	
	where a.ec_customer_id = 0;
	
#0
-- insert ygk_real.mst_customer
-- select a.*
-- from
-- ec_tmp.mst_customer as a
-- left join ygk_real.mst_customer as b
-- on a.customer_code= b.customer_code
-- where b.customer_code is null
-- and a.update_date >= NOW() - INTERVAL 10 MINUTE
-- ;
#1.update
-- 	update
-- 	ygk_real.mst_customer as a
-- 	join ec_tmp.mst_customer as b
-- 	set   a.department_type= b.department_type,
--   			a.customer_name=b.customer_name,
--   			a.company_name=b.company_name,
--   			a.company_name_abb=b.company_name_abb,
--   			a.department=  b.department,
--   			a.postal_code=b.postal_code,
--   			a.addr01=b.addr01,
--   			a.addr02=b.addr02,
--   			a.addr03=b.addr02,
--   			a.email=b.email,
--   			a.phone_number=b.phone_number,
--   			a.create_date=b.create_date,
--   			a.update_date=b.update_date
-- 	where a.customer_code= b.customer_code
--    and a.update_date >= NOW() - INTERVAL 10 MINUTE
-- ;
#=============================== 2.insert
-- 	insert into ygk_real.mst_customer
-- 	select a.*
-- 	from ec_tmp.mst_customer as a
-- 	left join ygk_real.mst_customer as b
-- 	on a.customer_code = b.customer_code
-- 	where b.customer_code is null
-- 	and a.update_date >= NOW() - INTERVAL 10 MINUTE 
-- 	;

	SET rows_count = ROW_COUNT();

	SET process_step_name = 'End process';	
	
	-- Write logs --
 CALL ec_tmp.write_logs(sp_name, process_step_name, code, msg, rows_count, ope_name);
	
END//
DELIMITER ;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
