developing


 [https://github.com/thirdgerb/chatbot](https://github.com/thirdgerb/chatbot) 库在laravel 里的基本实现, 使之在laravel可用. 现在只做了最简单的实现.

依赖:

- swoole (可选)
- predis
- mysql
- redis

安装方式, 在laravel (~5.8) 项目中:

    composer require commune/chatbot-laravel:dev-master

在 laravel 的  /config/app.php 的 providers 数组中添加 ``` \Commune\Chatbot\Laravel\ChatbotServiceProvider::class ```


运行 ``` artisan vendor:publish ```  将配置加载到laravel 目录下

运行 ``` artisan migrate ``` 创建chatbot 的数据表.

运行 ``` artisan commune:tinker ``` 可以运行命令行测试用例.

运行 ``` artisan commune:tcp ``` 可以基于swoole 运行机器人的 tcp 服务.
