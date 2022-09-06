#!/bin/bash
START=$(date +%s);
HOST='ygk-transport.csorc40ihjzl.ap-northeast-3.rds.amazonaws.com';
USER='admin';
PASS='#Ygk202208';

HOST_CLONE='ecdb01.csorc40ihjzl.ap-northeast-3.rds.amazonaws.com';
USER_CLONE='admin';
PASS_CLONE='#Ygk202208';

DB='master';
DB_CLONE='ygk_real';

CLONE_TABLES="
dt_price
";

CLONE_WHERE="update_date >= NOW() - INTERVAL 15 MINUTE";

CLONE_TABLES_SQL="
dt_price
";

nowdate=`date`;
echo "[$nowdate] Start clone";

[ -f "$DB.sql.gz" ] && rm "$DB.sql.gz"

mysqldump -h $HOST -u $USER --password=$PASS --default-character-set=utf8 $DB $CLONE_TABLES --where="$CLONE_WHERE" --single-transaction --skip-triggers --skip-add-drop-table --replace --no-create-info | gzip > $DB.sql.gz;

END=$(date +%s);
DIFF=$(( $END - $START ));
echo "master clone in $DIFF seconds";

zcat $DB.sql.gz | mysql -h $HOST_CLONE -u $USER_CLONE --password=$PASS_CLONE $DB_CLONE;

[ -f "$DB.sql.gz" ] && rm "$DB.sql.gz"

END=$(date +%s);
DIFF=$(( $END - $START ));
echo "dt_price end clone in $DIFF seconds";