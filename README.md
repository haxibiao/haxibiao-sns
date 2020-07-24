## 哈希表项目 sns 模块
负责基础社交能力
目前加入的模块有：
favorites，not_likes,comments,likes,follows,reports.tips

## 注意
sns从datizhuanqian.com重构，兼容的该项目逻辑


## 异常（Exception）：

GQLException，GQL 异常
UserException, 业务异常


## 安装步骤

1. `composer.json`改动如下：
   在`repositories`中添加 vcs 类型远程仓库指向
   `http://code.haxibiao.cn/packages/haxibiao-sns`
2. 执行`composer require haxibiao/sns`
3. 执行`php artisan sns:install && composer dump`
4. 执行`php artisan migrate`



