#!/bin/bash
START=$(date +%s);
HOST='ecdb01.csorc40ihjzl.ap-northeast-3.rds.amazonaws.com';
USER='admin';
PASS='#Ygk202208';

HOST_CLONE='ygk-transport.csorc40ihjzl.ap-northeast-3.rds.amazonaws.com';
USER_CLONE='admin';
PASS_CLONE='#Ygk202208';

DB='ygk_real';
DB_CLONE='master';

CLONE_TABLES="
dt_order
";

CLONE_WHERE="update_date >= NOW() - INTERVAL 10 MINUTE";

CLONE_TABLES_SQL="
dt_order
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
echo "dt_order end clone in $DIFF seconds";