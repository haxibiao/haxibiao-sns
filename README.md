## 哈希表项目 sns 模块

### 内容互动

1. Likable 点赞
2. Commentable 评论
3. Followable 关注
4. Favorable 收藏
5. Dislikable 不喜欢
6. Reportable 举报
7. Tipable 打赏
8. Visitable 足迹 （待重构）

### 用户互动

1. Notifiable 通知 (基于 Laravel)
2. Chat 聊天、消息

## 注意

sns 从旧项目重构，需要修复的一些 morph 字段变化

-   followed 重命名为 followable
-   liked 重命名为 likable
-   faved 重命名为 favorable
-   not_liked 重命名为 dislikable
-   commented 重命名为 commentable
-   reported 重命名为 reportable
-   tiped 重命名为 tipable

## 安装步骤

1. `composer.json`改动如下：
   在`repositories`中添加 vcs 类型远程仓库指向
   `http://code.haxibiao.cn/packages/haxibiao-sns`
2. 执行`composer require haxibiao/sns`
3. 执行`php artisan sns:install && composer dump`自动加载 service provider
4. 未集成 haxibiao-base 的 BaseUser 的 App\User 需使用 trait `WithSns`
