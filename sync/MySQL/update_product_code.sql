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

--  プロシージャ ec_tmp.update_product_code の構造をダンプしています
DELIMITER //
CREATE PROCEDURE `update_product_code`()
    DETERMINISTIC
BEGIN

-- 例外とエラー変数 
	DECLARE sp_name CHAR(100) DEFAULT 'update_product_code';
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

#=============================1.insert ec product	
replace ygk_real.dtb_product(creator_id,name,product_status_id,product_code,create_date,update_date,discriminator_type)
select 2,a.product_name,1,a.product_code,a.create_date,a.update_date,'product'
from ygk_real.mst_product as a
left join ygk_real.dtb_product as b
on a.ec_product_id = b.id
where b.id is null
;

#=============================2.insert ec category2
replace ygk_real.dtb_product_category(product_id,category_id,discriminator_type)
select a.id,ifnull(c.category_code2,'20'),'productcategory'
from ygk_real.dtb_product as a
join ygk_real.mst_product as c
on a.id = c.ec_product_id
left join ygk_real.dtb_product_category as b
on a.id = b.product_id
and b.category_id = c.category_code2
where
b.product_id is null
#=============================3.insert ec category1
;
replace ygk_real.dtb_product_category(product_id,category_id,discriminator_type)
select a.id,ifnull(c.category_code1,'1'),'productcategory'
from ygk_real.dtb_product as a
join ygk_real.mst_product as c
on a.id = c.ec_product_id
left join ygk_real.dtb_product_category as b
on a.id = b.product_id
and b.category_id = c.category_code1
where
b.product_id is null
;
#=============================4.insert ec product class
replace ygk_real.dtb_product_class(product_id,price01,sale_type_id,stock_unlimited,create_date,update_date,discriminator_type)
select a.id,0,1,1,a.create_date,a.update_date,'productclass'
from ygk_real.dtb_product as a
left join ygk_real.dtb_product_class as b
on a.id = b.product_id 
where b.product_id is null 
;
#=============================5.insert ec stock
SET process_step_name = 'set stock';
replace ygk_real.dtb_product_stock(product_class_id,creator_id,create_date,update_date,discriminator_type)
select a.id,2,a.create_date,a.update_date,'productstock'
from ygk_real.dtb_product_class as a
left join ygk_real.dtb_product_stock as b
on a.id = b.product_class_id
where 
b.id is null 
; 
#=============================6 .update ec=id　mst_product紐づけ
SET process_step_name = 'set ec_id';
update
ygk_real.mst_product as a
join ygk_real.dtb_product as b
on a.product_code = b.product_code
set a.ec_product_id = b.id
where a.ec_product_id = 0
; 
# 7 update tag
-- replace ygk_real.dtb_product_tag(product_id,tag_id,creator_id,create_date,discriminator_type)
-- select a.ec_product_id,b.id,1,current_time(),'tag'
-- from 
-- ygk_real.mst_product as a
-- join ygk_real.dtb_tag as b
-- on a.tag_code1 = b.name
-- where a.tag_code1 = 'PE'
-- ;	
#==================-- 
-- insert ygk_real.mst_product
-- select a.*
-- from
-- ec_tmp.mst_product as a
-- left join ygk_real.mst_product as b
-- on a.product_code= b.product_code
-- where b.product_code is null
-- and a.update_date >= NOW() - INTERVAL 10 MINUTE
-- ;

#0
-- update
-- ygk_real.mst_product as a
-- join ec_tmp.mst_product as b
-- on a.product_code = b.product_code
-- set
-- a.product_name=b.product_name,
-- a.product_name_abb=b.product_name_abb,
-- a.jan_code=b.jan_code,
-- a.unit_price=b.unit_price,
-- a.tag_code1=b.tag_code1,
-- a.tag_name1=b.tag_name1,
-- a.tag_code2=b.tag_code2,
-- a.tag_name2=b.tag_name2,
-- a.tag_code3=b.tag_code3,
-- a.tag_name3=b.tag_name3,
-- a.tag_code4=b.tag_code4,
-- a.tag_name4=b.tag_name4,
-- a.tag_code5=b.tag_code5,
-- a.tag_name5=b.tag_name5,
-- a.category_code1=b.category_code1,
-- a.category_name1=b.category_name1,
-- a.category_code2=b.category_code2,
-- a.category_name2=b.category_name2,
-- a.category_code3=b.category_code3,
-- a.category_name3=b.category_name3,
-- a.series_name=b.series_name,
-- a.line_no=b.line_no,
-- a.quantity=b.quantity,
-- a.size=b.size,
-- a.color=b.color,
-- a.material=b.material,
-- a.model=b.model
-- where a.product_code = b.product_code
-- and a.update_date >= NOW() - INTERVAL 30 MINUTE
-- ;

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
