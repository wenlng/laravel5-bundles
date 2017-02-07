Laravel 5 Bundles-Modules
==============

### Bundles-Modules 是一个把应用分成多个包，一个包分成若干个小模块.

## 安装 Installation
使用composer安装，把下面代码加入你的composer.json文件里：

To install through composer, simply put the following in your composer.json file:

```json
{
    "require": {
        "awen/bundles": "1.0"
    }
}
```

然后运行 `composer install` 获取包

And then run `composer install` to fetch the package.

或者 or
```
composer require "awen/bundles:1.0"
```

## 添加服务提供者 Add Service Provider
下一步添加以下服务提供者到 `config/app.php` 文件里

Next add the following service provider in `config/app.php`.

```php
'providers' => array(
  'Awen\Bundels\BundlesServiceProvider',
),
```

下一步刷新运行包的配置文件

Next publish the package's configuration file by run :

```
php artisan vendor:publish
```

## 配置自动加载  Autoloading
默认的控制器，实体或库没有自动加载，你可以用 `psr-4` 加载需要的东西。例如:

By default controllers, entities or repositories not loaded automatically. You can autoload all that stuff using `psr-4`. For example :

```json
{
  "autoload": {
    "psr-4": {
      "App\\": "app/",
      "Bundles\\": "bundles/"
    }
  }
}
```


## <<Bundles-Modules 目录结构>>
```
bundles
  ├── [Frontend|Backend|Wechat|Mobile|Api]
    ├── Modules/
      ├── Home/
        ├── Assets/
        ├── Assets/
        ├── Console/
        ├── Database/
          ├── Migrations/
          ├── Seeders/
        ├── Entities/
        ├── HttpApi/
          ├── Controllers/
          ├── Middleware/
          ├── Requests/
          ├── routes.php
        ├── HttpView/
          ├── Controllers/
          ├── Middleware/
          ├── Requests/
          ├── routes.php
        ├── Providers/
           ├── HomeServiceProvider.php
        ├── Resources/
          ├── lang/
          ├── views/
            ├── api/
            ├── home/
        ├── Repositories/
        ├── Middleware/
        ├── Events/
        ├── Listeners/
        ├── Jobs/
        ├── Exceptions/
        ├── composer.json
        ├── HomeModule.php
    ├── Services/
      ├── Service.php
    ├── composer.json
    ├── [Frontend|Backend]Bundle.php
```



## 下载完成后开始使用  After the download is complete

- [详细使用教程 (Detailed tutorial)  Url : http://www.lwgblog.com](http://www.lwgblog.com)

### 一、简单注册Bundle与Module：
1.创建一个Bundle
```
php artisan bundle:make Frontend
```
2.创建Bundle后需要注册才能加载使用，在Bundle目录下AppKernel.php里的registerBundles()方法注册Bundle
```
  return [
    \Bundles\Frontend\FrontendBundle::class,
  ];
```
3.完成Bundle后需要创建Module
```
php artisan bundle:make-module Home -b=Frontend
```

4.Module也一样需要注册才能加载使用，在Frontend目录下FrontendBundle.php里的registerModules()方法注册Module
```
return [
  \Bundles\Frontend\Modules\HomeModule::class,
];
```

5.在浏览器打开url: ..../frontend/home就会显示Hello World，说明注册成功了


### 二、简单使用服务：
1.创建一个服务，在目录生成一个服务文件并生成配置文件在Config目录里
```
php artisan bundle:make-service tool -b=Frontend -c
```
2.服务一样需要注册才能使用，在Services目录下的Service.php的registerServices()里注册
```
'tool' => [
  'class' => \Bundles\Frontend\Services\Tool\ToolService::class,
  'config' => __DIR__ . '/Tool/Config/config.php'
 ],
```
3.创建服务需要加载的类，在Tool目录下创建一个PayClass测试类，里面写上一个show测试方法
```
class PayClass{
    function show(){
        dd('pay-show');
    }
}
```
4.然后把需要加载的类加载到服务里使用，在刚才创建的服务，Tool目录下有个ToolService.php的registerClassFiles()里注册
```
return [
  'pay' =>[
   'class' => \Bundles\Frontend\Services\Tool\PayClass::class, //这是类
   'param' => [   ] //这是类里的构造参数，参数下面详情讲解
  ]
]
```

5.在任何一个控制器controller里新建一个构造方法依赖注入服务( Bundles\Frontend\Services\Service )即可使用：
```
use Bundles\Frontend\Services\Service;
class IndexController extends Controller{
  protected $service;
  public function __construct(Service $servic){
    $this->service = $servic;
  }

  public function index(){
    //获取服务形式下面详情讲
    $tool_service = $this->service->getService('frontend:tool');
    //执行服务里的类方法
    $tool_service->execute('pay.show');
  }
}
```

### 三、服务注册类文件registerClassFiles()：
1.注册格式
```
'类名称' =>[
   'class' => 类
   'param' => 类里的构造参数，必须是数组
  ]
```
2.param参数填写规则：
```
%name% 表示在config.php里的name变量参数
@name  表示已存在的服务类名字，需要注入一个类，《单例》
@name@ 表示已存在的服务类名字，需要注入一个类，《不单例》
name   表示普通字符串参数
```
3.拿上面的Tool服务再加一个类做实践，在Tool目录下新建一个StrClass类test方法：
```
use Bundles\Frontend\Services\Tool\PayClass;
class StrClass{
  protected $pay;
  protected $name;

  function __construct(Pay $pay, $name){
    $this->pay = $pay;
    $this->name = $name;
  }
  function show(){
    dd($this->$pay);
  }
}
```

4.注册这个服务类并填写相关参数，还是在Tool目录下ToolService.php的registerClassFiles()里注册
```
return [
  'str' =>[
   'class' => \Bundles\Frontend\Services\Tool\StrClass::class,
   'param' => ['@pay', 'Awen']
  ]
]
```
``` 注意：@pay 表示依赖注入PayClass实例，左边一个@表示是同一个实例，左右一个@表示不是同一个实例```

5.在控制器里就可以使用了
```
use Bundles\Frontend\Services\Service;
class IndexController extends Controller{
  protected $service;
  public function __construct(Service $servic){
    $this->service = $servic;
  }

  public function index(){
    //获取服务形式下面详情讲
    $tool_service = $this->service->getService('frontend:tool');
    //执行服务里的类方法
    $tool_service->execute('str.test');
  }
}
```
``` 注意：getService('frontend:tool') 有第二个参数，默认是false，表示无论获取多少次这个服务都是同一个实例，如果第二个参数为true时，第二次后获取的不是同一个服务实例，是一个新的服务实例```

``` 其他方式获取服务：$this->getService('frontend:tool') 一样的效果```

### 四、控制器基类Controller：
1.加载视图格式： 'frontend@home::view.index' //BundleName@ModuleName::view....
2.$this->getService('frontend:tool'),获取服务，与$this->service->getService('frontend:tool')一样，参数也一样
3.$this->getBundle($bundle_name); //传入名称获取单个Bundle，不传参数表示获取所有Bundle
4.$this->getModule($bundle_name,$module_name); //传入Bundle名称，获取所有Module，传入Module名称获取单个Module
5.$this->getCurrentBundle() //获取当前的Bundle，注意目录结构不能随意修改
6.$this->getCurrentModule() //获取当前的Module，注意目录结构不能随意修改
7.$this->getUseTime() //获取当前内核处理所有Bundle的时间
8.$this->getBundleParam($bundle_name) //获取当前Bundle的相关参数
9.$this->hasBundle($bundle_name);   //检查Bundle是否存在
10.$this->hasModule($bundle_name, $module_name);   //检查Bundle的Module是否存在
11.$this->getModuleRegisterParam($bundle, $module, $name);   //获取某个Bundle里注册Module的相关参数 $name 可以是：path、name、bundle_name、parameter、routes、aliases、providers、route_middleware、groups_middleware、events、subscribes、consoles

### 五、Bundle可以操作的方法：
$bundle = $this->getCurrentBundle();
$bundle->getLoweName(); //获取名称
$bundle->getPath(); //获取路径
$bundle->getParam(); //获取参数
$bundle->getModuleParam($module_name, $name); //获取Module相关参数，$name 与 $this->getModuleRegisterParam($bundle, $module, $name);的 $name 一样
$bundle->getModules(); //获取所有Module
$bundle->getModule($name); //检查某个Module
$bundle->getServices(); //获取所有Services
$bundle->hasModule($name); //检查Module是否存在

### 六、Module可以操作的方法：
$module = $this->getCurrentModule();
$module->getLoweName(); //获取名称
$module->getPath(); //获取路径
$module->getModuleKey(); //获取当前Bundle与Module结合的key
$module->getRegisterParam($name); //获取Module相关参数，$name 与 $this->getModuleRegisterParam($bundle, $module, $name);的 $name 一样



## << 全部命令行操作命令 >>

**1-1.生成单个Bundle：**
```
php artisan bundle:make <BundleName>
```

**1-2.生成多个Bundle：**
```
php artisan bundle:make <BundleName> <BundleName> ...
```

**1-3.生成Bundle时，如果遇到Bundle存在时，-f (--force)表示强制生成，会把旧的删除，再重新生成：**
```
php artisan bundle:make <BundleName> -f
```

**2-1.指定一个Bundle生成单个Module：**
```
php artisan bundle:make <ModuleName> -b=<BundleName>
```

**2-2.指定一个Bundle生成多个Module：**
```
php artisan bundle:make <ModuleName> <ModuleName> ... -b=<BundleName>
```

**2-3.指定一个Bundle生成Module，-c (--clean)不生成小实例(路由+控制器+视图文件)：**
```
php artisan bundle:make-module <ModuleName> -b=<BundleName> -c
```

**2-4.指定一个Bundle生成Module，如果遇到Module存在时，-f (--force)表示强制生成，会把旧的删除，再重新生成：**
```
php artisan bundle:make-module <ModuleName> -b=<BundleName> -f
```

**3-1.指定一个Bundle的Module，生成单个Controller：**
```
php artisan bundle:make-controller <ControllerName> -b=<BundleName> -m=<ModuleName>
```

**3-2.指定一个Bundle的Module，生成多个Controller：**
```
php artisan bundle:make-controller <ControllerName> <ControllerName> ... -b=<BundleName> -m=<ModuleName>
```

**3-3.指定一个Bundle的Module，生成Controller,-c (--cate)指定分类下的Controllers {  a(api) | n(view) } ：**
```
artisan bundle:make-controller <ControllerName> -b=<BundleName> -m=<ModuleName> -c=<a|v>
```

**4-1.指定一个Bundle的Module，生成单个Command ：**
```
artisan bundle:make-command <CommandName> -b=<BundleName> -m=<ModuleName>
```

**4-2.指定一个Bundle的Module，生成多个Command ：**
```
artisan bundle:make-command <CommandName> <CommandName> ... -b=<BundleName> -m=<ModuleName>
```

**4-3.指定一个Bundle的Module，生成多个Command，-c (--command)并指定命令的名称 ：**
```
php artisan bundle:make-command <CommandName> -b=<BundleName> -m=<ModuleName> -c=<command:name>
```

**5-1.指定一个Bundle的Module，生成单个Model ：**
```
php artisan bundle:make-model <ModelName> -b=<BundleName> -m=<ModuleName>
```

**5-2.指定一个Bundle的Module，生成多个Model ：**
```
php artisan bundle:make-model <ModelName> <ModelName> .... -b=<BundleName> -m=<ModuleName>
```

**5-3.指定一个Bundle的Module，生成Model, -c (--cate)指定生成Model还是Repository { m (model) | r (repository) }：**
```
php artisan bundle:make-model <ModelName> -b=<BundleName> -m=<ModuleName> -c=<m|r>
```

**5-4.指定一个Bundle的Module，-a (--all) 生成Model和Repository：**
```
php artisan bundle:make-model <ModelName> -b=<BundleName> -m=<ModuleName> -a
```

**5-5.指定一个Bundle的Module，生成Model和Repository，-i (--id)指定Model的ID主键：**
```
php artisan bundle:make-model <ModelName> -b=<BundleName> -m=<ModuleName> -i=<ModelId>
```

**6-1.指定一个Bundle的Module，在module目录下的Middleware生成单个Middleware ：**
```
php artisan bundle:make-middleware <MiddlewareName> -b=<BundleName> -m=<ModuleName>
```

**6-2.指定一个Bundle的Module，在module目录下的Middleware生成多个Middleware ：**
```
php artisan bundle:make-middleware <MiddlewareName> <MiddlewareName> ... -b=<BundleName> -m=<ModuleName>
```

**6-3.指定一个Bundle的Module，-c (--cate)指定是在 a(api) | v(view) | r(module) 哪个的Middleware目录下生成：**
```
php artisan bundle:make-middleware <MiddlewareName> -b=<BundleName> -m=<ModuleName> -c=<a|v|r>
```

**7-1.指定一个Bundle的Module，生成单个Provider：**
```
php artisan bundle:make-provider <ProviderName> -b=<BundleName> -m=<ModuleName>
```

**7-2.指定一个Bundle的Module，生成多个Provider：**
```
php artisan bundle:make-provider <ProviderName> <ProviderName> ... -b=<BundleName> -m=<ModuleName>
```

**8-1.指定一个Bundle的Module，生成单个Job：**
```
php artisan bundle:make-job <JobName> -b=<BundleName> -m=<ModuleName>
```

**8-2.指定一个Bundle的Module，生成单个Job：**
```
php artisan bundle:make-job <JobName> <JobName> ... -b=<BundleName> -m=<ModuleName>
```

**9-1.指定一个Bundle下的服务，生成单个服务：**
```
php artisan bundle:make-service <ServiceName> -b=<BundleName>
```

**9-2.指定一个Bundle下的Service，生成多个Service：**
```
php artisan bundle:make-service <ServiceName> <ServiceName> ... -b=<BundleName>
```

**10-1.指定一个Bundle的Module，生成单个Event：**
```
php artisan bundle:make-event <EventName> -b=<BundleName> -m=<ModuleName>
```

**10-2.指定一个Bundle的Module，生成多个Event：**
```
php artisan bundle:make-event <EventName> <EventName> ... -b=<BundleName> -m=<ModuleName>
```

**11-1.指定一个Bundle的Module，生成单个Listener，-e (--event)需要Event名称或Event命名空间：**
```
php artisan bundle:make-listener <ListenerName> -b=<BundleName> -m=<ModuleName> -e=<EventName>
php artisan bundle:make-listener <ListenerName> -b=<BundleName> -m=<ModuleName> -e=<EventNamespace>
```

**12-1.指定一个Bundle的Module，生成在Module里注册的(Event+Listener)：**
```
php artisan bundle:generate-event
```

**13-1.指定一个Bundle，所有Module的asset资源刷新生成asset资源到public下：**
```
php artisan bundle:publish -b=<BundleName>
```

**13-2.指定一个Bundle的Module的asset资源，刷新生成asset资源到public下：**
```
php artisan bundle:publish -b=<BundleName> -m=<ModuleName>
```

**14-1.指定一个Bundle，所有Module的lang资源刷新生成lang资源到resources/lang下：**
```
php artisan bundle:publish-lang -b=<BundleName>
```

**14-2.指定一个Bundle的Module的lang资源，刷新生成lang资源到resources/lang下：**
```
php artisan bundle:publish-lang -b=<BundleName> -m=<ModuleName>
```

**15-1.指定一个Bundle，所有Module的migration资源刷新生成migration资源到databases/migration下：**
```
php artisan bundle:publish-migration -b=<BundleName> -m=<ModuleName>
```

**15-2.指定一个Bundle的Module的migration资源，刷新生成migration资源到databases/migration下：**
```
php artisan bundle:publish-migration -b=<BundleName> -m=<ModuleName>
```

**16-1.指定一个Bundle的Module，生成 create 操作的Migration：**
```
php artisan bundle:make-migration create_<TABLE_NAME>_table -b=<BundleName> -m=<ModuleName>
```

**16-2.指定一个Bundle的Module，生成 remove 操作的Migration：**
```
php artisan bundle:make-migration remove_<COLUMN_NAME>_from_<TABLE_NAME>_table -b=<BundleName> -m=<ModuleName>
```

**16-3.指定一个Bundle的Module，生成 add 操作的Migration：**
```
php artisan bundle:make-migration add_<COLUMN_NAME>_to_<TABLE_NAME>_table -b=<BundleName> -m=<ModuleName>
```

**16-4.指定一个Bundle的Module，生成 drop 操作的Migration：**
```
php artisan bundle:make-migration drop_<TABLE_NAME>_table -b=<BundleName> -m=<ModuleName>
```

**16-4.create/remove/add/drop...，后带字段参数时，字段之间用逗号 (,) 分隔：**
```
... -f="<COLUMN_NAME>:string, <COLUMN_NAME>:string"
```

**16-5.指定一个Bundle的Module，生成对应操作的Migration，-f (--field)指定字段，多字段之间用 (,) 逗号相隔：**
```
php artisan bundle:make-migration <OPERATION_NAME>_<TABLE_NAME>_table -b=<BundleName> -m=<ModuleName>
```

**17-1.指定Bundle下的Module生成seeder，默认是生成'TableSeeder'：**
```
php artisan bundle:make-seed <SeedName> -b=<BundleName> -m=<ModuleName>
```

**17-2.指定Bundle下的Module生成seeder，-d 生成'DatabaseSeeder'：**
```
php artisan bundle:make-seed <SeedName> -b=<BundleName> -m=<ModuleName> -d
```

**18-1.指定Bundle，执行migrate：**
```
php artisan bundle:migrate -b=<BundleName>
```

**18-1.指定Bundle的Module，执行migrate：**
```
php artisan bundle:migrate -b=<BundleName> -m=<ModuleName>
```

**18-3.指定Bundle的Module，执行migrate，可带与框架自带的一些参数：**
```
php artisan bundle:migrate -b=<BundleName> -m=<ModuleName> --database --pretend --force --seed
```

**19-1.指定Bundle，执行seed：**
```
php artisan bundle:seed -b=<BundleName>
```

**19-2.指定Bundle的Module，执行seed：**
```
php artisan bundle:seed -b=<BundleName> -m=<ModuleName>
```

**19-3.指定Bundle的Module，执行seed，可带与框架自带的一些参数：**
```
php artisan bundle:seed -b=<BundleName> -m=<ModuleName> --database --class
```

**20-1.指定Bundle，执行rollback：**
```
php artisan bundle:migrate-rollback -b=<BundleName>
```

**20-2.指定Bundle的Module，执行rollback：**
```
php artisan bundle:migrate-rollback -b=<BundleName> -m=<ModuleName>
```

**21-1.指定Bundle，执行reset：**
```
php artisan bundle:migrate-reset -b=<BundleName>
```

**21-2.指定Bundle的Module，执行reset：**
```
php artisan bundle:migrate-reset -b=<BundleName> -m=<ModuleName>
```

**22-1.指定Bundle，执行refresh：**
```
php artisan bundle:migrate-refresh -b=<BundleName>
```

**22-2.指定Bundle的Module，执行refresh：**
```
php artisan bundle:migrate-refresh -b=<BundleName> -m=<ModuleName>
```

**22-3.指定Bundle的Module，执行refresh，与框架一些自带的参数：**
```
php artisan bundle:migrate-refresh -b=<BundleName> -m=<ModuleName> --database --force --seed
```