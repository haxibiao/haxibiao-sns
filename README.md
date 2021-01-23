# 哈希表项目 sns 模块

## 内容互动 (WithSns)

1. Likable (用户,内容,专题的)点赞特性（重构ing）
2. Commentable (用户,内容,专题的)评论特性（重构ing）
3. Followable (用户,内容,专题的)关注特性（重构ing）
4. Favorable (用户,内容,专题的)收藏特性（重构ing）
5. Dislikable (用户,内容,专题的)不感兴趣特性（done）
6. Reportable (用户,内容,专题的)举报特性（重构ing）
7. Tippable (用户,内容,专题的)打赏特性（重构ing）
8. Visitable (用户,内容,专题的)足迹特性（待重构）

## 用户互动 (UseSns)

1. Notifiable 通知 (基于 Laravel)
2. Chat 聊天、消息

## 注意

sns 从旧项目重构，需要修复的一些 morph 字段(特性)变化

- follows 表字段 followed 重命名为 followable
- likes 表字段 liked 重命名为 likeable
- favorites 表字段 faved 重命名为 favorable
- dislikes 表字段 not_liked 重命名为 dislikeable
- comments 表字段 commented 重命名为 commentable
- reports 表字段 reported 重命名为 reportable
- tips 表字段 tiped 重命名为 tippable
- visits 表字段 visited 重命名为 visitable

## 安装步骤

1. `composer.json`改动如下：
   在`repositories`中添加 vcs 类型远程仓库指向
   `http://code.haxibiao.cn/packages/haxibiao-sns`
2. 执行`composer require haxibiao/sns`
3. 执行`php artisan sns:install && composer dump`自动加载 service provider


## TODOs
- gql里引用question的部分 需要重构
