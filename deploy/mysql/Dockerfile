FROM ccr.ccs.tencentyun.com/library/mysql:8.0

# 字符集配置（与业务库一致）
COPY my.cnf /etc/mysql/conf.d/

# 首次启动（空数据目录）时自动执行建表脚本
COPY 001_wechat_user_tables.sql /docker-entrypoint-initdb.d/

EXPOSE 3306
