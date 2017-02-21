Laravel 5 Bundles
==============

### Bundles 是一个把应用包系统，从2.0.0版本开始...

使用这个Bundles系统可以抛弃自带的APP目录了，使用全新的开发目录结构/，使开发变目录分离得更简单、使用应用强内聚、松耦合


## 安装 Installation
使用composer安装，把下面代码加入你的composer.json文件里：

To install through composer, simply put the following in your composer.json file:

```json
{
    "require": {
        "awen/bundles": "~2.0.0"
    }
}
```

然后运行 `composer install` 获取包

And then run `composer install` to fetch the package.

或者 or
```
composer require "awen/bundles:~2.0.0"
```


## 添加服务提供者 Add Service Provider
下一步添加以下服务提供者到 `config/app.php` 文件里

Next add the following service provider in `config/app.php`.

```php
'providers' => [
  Awen\Bundles\BundlesServiceProvider::class,
],
```

如何出现命名空间不存在，执行一下： `composer dump-autoload`

下一步刷新运行包的配置文件

Next publish the package's configuration file by run :

```
php artisan vendor:publish --tag=config --force
```

## 配置自动加载  Autoloading
默认的 控制器，实体或库 没有自动加载，你可以用 `psr-4` 加载需要的东西。例如:

By default controllers, entities or repositories not loaded automatically. You can autoload all that stuff using `psr-4`. For example :

```json
{
  "autoload": {
    "psr-4": {
      "Bundles\\": "bundles/"
    }
  }
}
```


## < Bundles开发目录结构 >
```
bundles
    ├── [Home|Admin|Wechat|Mobile|Api|...]
  	    ├── Assets/             //前端资源
  	    ├── Console/            //命令行
  	    ├── Database/           //数据库迁移控制
  	        ├── Migrations/
  	        ├── Seeders/
  	    ├── Entities/           //Model实体
  	    ├── HttpApi/            //PC网页操作api
  	        ├── Controllers/
  	        ├── Middleware/
  	        ├── Requests/
  	        ├── routes.php
  	    ├── HttpView/           //PC网页展示view
  	        ├── Controllers/
  	        ├── Middleware/
  	        ├── Requests/
  	        ├── routes.php
  	    ├── Providers/          //服务提供者
  	        ├── HomeServiceProvider.php     //自带服务
  	    ├── Resources/          //资源
  	        ├── lang/
  	        ├── views/
  	            ├── api/
  	            ├── view/
  	    ├── Repositories/       //Model仓库
  	    ├── Middleware/         //api与view公用中间件
  	    ├── Events/             //事件
  	    ├── Listeners/          //监听器
  	    ├── Jobs/               //队列
  	    ├── Exceptions/         //异常处理
        ├── Services/           //服务
            ├── Service.php     //注册服务
            ├── //....
        ├── composer.json
        ├── [Home|Admin|Wechat|Mobile|Api|...]Bundle.php
    ├── AppKernl.php    //注册Bundle
```


## 下载完成后开始使用  After the download is complete

- [详细使用教程 (Detailed tutorial)  Url : http://www.lwgblog.com](http://www.lwgblog.com)
- laravel技术交流群：178498936
- 作者联系QQ：871024608

### 一、简单注册Bundle：
1.创建一个前端(Home)的Bundle
```
php artisan bundle:make Home
```
2.创建Bundle后需要注册才能加载使用，在Bundle目录下AppKernel.php里的registerBundles()方法注册Bundle
```
  return [
    \Bundles\Home\HomeBundle::class,
  ];
```
3.在浏览器打开url: ..../home就会显示 Hello World，说明注册成功了


### 二、简单使用Bundle中的服务：
1.创建一个服务，在目录生成一个服务文件并生成配置文件在Config目录里
```
php artisan bundle:make-service tool -b=Home -c
```
2.服务一样需要注册才能使用，在Services目录下的Service.php的registerServices()里注册
```
'tool' => [
  'class' => \Bundles\Home\Services\Tool\ToolService::class,
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
   'class' => \Bundles\Home\Services\Tool\PayClass::class, //这是类
   'param' => [] //这是类里的构造参数，参数下面详情讲解
  ]
]
```

5.在任何一个控制器controller里新建一个构造方法依赖注入服务( Bundles\Home\Services\Service )即可使用：
```
use Bundles\Home\Services\Service;
class IndexController extends Controller{
  protected $service;
  public function __construct(Service $servic){
    $this->service = $servic;
  }

  public function index(){
    //获取服务形式下面详情讲
    $tool_service = $this->service->getService('home:tool');
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
[param] 表示获取所有config.php参数
name   表示普通字符串参数
```
3.拿上面的Tool服务再加一个类做实践，在Tool目录下新建一个StrClass类test方法：
```
use Bundles\Home\Services\Tool\PayClass;
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
   'class' => \Bundles\Home\Services\Tool\StrClass::class,
   'param' => ['@pay', 'Awen']
  ]
]
```
``` 注意：@pay 表示依赖注入PayClass实例，左边一个@表示是同一个实例，左右一个@表示不是同一个实例```


5.在控制器里就可以使用了
```
use Bundles\Home\Services\Service;
class IndexController extends Controller{
  protected $service;
  public function __construct(Service $servic){
    $this->service = $servic;
  }

  public function index(){
    //获取服务形式下面详情讲
    $tool_service = $this->service->getService('home:tool');
    //执行服务里的类方法
    $tool_service->execute('str.test');

    或者：
    $tool_service_str = $this->service->getService('home:tool')->make('str');
    $tool_service_str->test();
  }
}
```
``` 注意：getService('home:tool') 有第二个参数，默认是false，表示无论获取多少次这个服务都是同一个实例，如果第二个参数为true时，第二次后获取的不是同一个服务实例，是一个新的服务实例```

``` 当$this->getService('home:tool',true) 第二个参数为true时表示获取新的服务对象```

``` 与继承基类controller 的 $this->getService('home:tool') 一样的效果```

``` 服务内部类仅可以依懒注入另外一个Bundle的Service与Model仓库，不能注入另外一个Bundle的服务内部类 ```


### 四、控制器基类Controller：
1.加载视图格式： 'home::view.index' //格式BundleName::view....

2.$this->getService('home:tool'),获取服务，与$this->service->getService('home:tool')一样，参数也一样

3.$this->getBundle($bundle_name); //传入名称获取单个Bundle，不传参数表示获取所有Bundle

4.$this->getCurrentBundle() //获取当前的Bundle，注意目录结构不能随意修改

5.$this->getUseTime() //获取当前内核处理所有Bundle的时间

6.$this->getBundleParam($bundle_name) //获取当前Bundle的相关参数

7.$this->hasBundle($bundle_name);   //检查Bundle是否存在

8.$this->getBundleRegisterParam($bundle, $name);   //获取某个Bundle里注册的相关参数 $name 可以是：path、name、parameter、routes、aliases、providers、route_middleware、groups_middleware、events、subscribes、consoles

9.$this->getAssetUrl()	//获取当前Bundle资源根url

10.$this->getStoragePath()	//获取资源存储根path

### 五、Bundle可以操作的方法：
$bundle = $this->getCurrentBundle();    //获取当前Bundle

$bundle->getLoweName(); //获取名称

$bundle->getPath(); //获取路径

$bundle->getParam(); //获取参数

$bundle->getRegisterParam($name); //获取相关参数，$name与$this->getBundleRegisterParam($bundle, $name);一样

$bundle->getServices(); //获取所有Services

$bundle->getAssetUrl()	//获取当前Bundle资源根url

$bundle->getStoragePath()	//获取资源存储根path


### 七、仓库（Model|Repository）模式使用：
1.创建Model和Repository，-a表示两个同时生成，在Home目录下的Entities创建TestModel，和Repositories目录创建TestRepository
```
php artisan bundle:make-model test -b=Home -a
```

2.在任何控制器中使用Repository
```
use Bundles\Home\Repositories\TestRepository;;
class IndexController extends Controller{
  protected $test;
  public function __construct(TestRepository $test){
    $this->test = $test;
  }

  public function index(){
    dd($this->test->find(1));
  }
}
```


### 八、仓库（Model|Repository）模式自带一些使用：
1.->all($columns)  //获取全部数据，$columns可选

2.->first($columns)  //获取全部数据，$columns可选

3.->paginate($limit = 10, array $order_by = [], array $where = [], $columns = ['*'], $method = "paginate")

4.->whereJoinPaginate(array $where, array $join, $limit = 10, array $order_by = [], $columns = ['*'], $method = "paginate")

5.->find($id, $columns = ['*'])

6.->findOneByField($field, $value, $columns = ['*'])

7.->findByField($field, $value, $limit = null, $columns = ['*'], array $order_by = [])

8.->findOneWhere(array $where, $columns = ['*'])

9.->findWhere(array $where, $limit = null, $columns = ['*'], array $order_by = [])

10.->findOneOrWhere(array $where, $columns = ['*'])

11.->findOrWhere(array $where, $limit = null, $columns = ['*'], array $order_by = [])

12.->findWhereIn($field, array $values, $limit = null, $columns = ['*'])

13.->findWhereNotIn($field, array $values, $limit = null, $columns = ['*'])

14.->findWhereJoin(array $where, array $join, $limit = null, $columns = ['*'], array $order_by = [])

15.->findOneWhereJoin(array $where, array $join, $columns = ['*'])

16.->sumField(array $where, $field)

17.->countField(array $where = [])

18.->orderBy($column, $direction = 'desc', $limit = null, $columns = ['*'])

19.->create(array $attributes, $object = false)

20.->update(array $attributes, $id)

21.->updateByField(array $attributes, $field, $value)

22.->delete($id)

23.->whereDelete(array $where = [])

#### where填写格式：
```
$where = [ 'where1' => 1, 'where2' => 2 ]

$where = [
    ['where1', '<>', 1],
    ['where2', '<>', 2]
]
```

#### orderBy填写格式：
```
$order_by = [ 'cate_order' => 'desc' ]
```

#### join填写格式：
```
$join = [
    [ 'table1', 'table2.id', '=', 'table1.id' ],
    [ 'table1', 'table2.id', '=', 'table1.id' ]
]
```

#### create填写格式：
```
$data = [
    'name' => 'hello',
    'age' => 20
]
```

#### update填写格式：
```
$update = [
    'name' => 'new_name',
    'age' => 'new_20'
]
```


##
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

**2-1.指定一个Bundle，生成单个Controller，默认在ViewHttp下：**
```
php artisan bundle:make-controller <ControllerName> -b=<BundleName>
```

**2-2.指定一个Bundle，生成多个Controller，默认在ViewHttp下：**
```
php artisan bundle:make-controller <ControllerName> <ControllerName> ... -b=<BundleName>
```

**2-3.指定一个Bundle，生成Controller,-c (--cate)指定分类下的Controllers {  a(ApiHttp) | v(ViewHttp) } ：**
```
artisan bundle:make-controller <ControllerName> -b=<BundleName> -c=<a|v>
```

**2-4.指定一个Bundle，生成Controller, -p (--path) 在Controller目录带目录,例如：-p=index 就会在对应Controller目录下的index目录下生成Controller ：**
```
artisan bundle:make-controller <ControllerName> -b=<BundleName> -p=index
```

**2-5.指定一个Bundle，生成Controller, -e(--extend) 指定继承对应的基类Controller，例如当前生成的Controller继承CommonController：**
```
artisan bundle:make-controller <ControllerName> -b=<BundleName> -e=...\CommonController
```

**3-1.指定一个Bundle，生成单个Command ：**
```
artisan bundle:make-command <CommandName> -b=<BundleName>
```

**3-2.指定一个Bundle，生成多个Command ：**
```
artisan bundle:make-command <CommandName> <CommandName> ... -b=<BundleName> 
```

**3-3.指定一个Bundle，生成多个Command，-c (--command)并指定命令的名称 ：**
```
php artisan bundle:make-command <CommandName> -b=<BundleName> -c=<command:name>
```

**4-1.指定一个Bundle，生成单个Model ：**
```
php artisan bundle:make-model <ModelName> -b=<BundleName>
```

**4-2.指定一个Bundle，生成多个Model ：**
```
php artisan bundle:make-model <ModelName> <ModelName> .... -b=<BundleName> 
```

**4-3.指定一个Bundle，生成Model, -c (--cate)指定生成Model还是Repository { m (model) | r (repository) }：**
```
php artisan bundle:make-model <ModelName> -b=<BundleName> -c=<m|r>
```

**4-4.指定一个Bundle，-a (--all) 生成Model和Repository：**
```
php artisan bundle:make-model <ModelName> -b=<BundleName> -a
```

**4-5.指定一个Bundle，生成Model和Repository，-i (--id)指定Model的ID主键：**
```
php artisan bundle:make-model <ModelName> -b=<BundleName> -a -i=<ModelId>
```

**5-1.指定一个Bundle，默认在bundle目录下的Middleware生成单个Middleware ：**
```
php artisan bundle:make-middleware <MiddlewareName> -b=<BundleName>
```

**5-2.指定一个Bundle，默认在bundle目录下的Middleware生成多个Middleware ：**
```
php artisan bundle:make-middleware <MiddlewareName> <MiddlewareName> ... -b=<BundleName>
```

**5-3.指定一个Bundle，-c (--cate)指定是在 a(ApiHttp) | v(ViewHttp) | r(Bundle) 下的Middleware目录下生成：**
```
php artisan bundle:make-middleware <MiddlewareName> -b=<BundleName> -c=<a|v|r>
```

**6-1.指定一个Bundle，默认在View目录下的Request生成：**
```
php artisan bundle:make-request <RequestName> -b=<BundleName>
```

**6-2.指定一个Bundle，-c (--cate)指定是在 a(ApiHttp) | v(ViewHttp) 下的Request目录下生成：**
```
php artisan bundle:make-request <RequestName> -b=<BundleName> -c=<a|v>
```

**7-1.指定一个Bundle，生成单个Provider：**
```
php artisan bundle:make-provider <ProviderName> -b=<BundleName>
```

**7-2.指定一个Bundle，生成多个Provider：**
```
php artisan bundle:make-provider <ProviderName> <ProviderName> ... -b=<BundleName>
```

**8-1.指定一个Bundle，生成单个Job：**
```
php artisan bundle:make-job <JobName> -b=<BundleName>
```

**8-2.指定一个Bundle，生成多个Job：**
```
php artisan bundle:make-job <JobName> <JobName> ... -b=<BundleName>
```

**9-1.指定一个Bundle下的服务，生成单个服务：**
```
php artisan bundle:make-service <ServiceName> -b=<BundleName>
```

**9-2.指定一个Bundle下的Service，生成多个Service：**
```
php artisan bundle:make-service <ServiceName> <ServiceName> ... -b=<BundleName>
```

**10-1.指定一个Bundle，生成单个Event：**
```
php artisan bundle:make-event <EventName> -b=<BundleName>
```

**10-2.指定一个Bundle，生成多个Event：**
```
php artisan bundle:make-event <EventName> <EventName> ... -b=<BundleName>
```

**11-1.指定一个Bundle，生成单个Listener，-e (--event)需要Event名称或Event命名空间：**
```
php artisan bundle:make-listener <ListenerName> -b=<BundleName> -e=<EventName>
php artisan bundle:make-listener <ListenerName> -b=<BundleName> -e=<EventNamespace>
```

**12-1.指定一个Bundle，生成在<BundleName>Bundle.php里注册的(Event+Listener)：**
```
php artisan bundle:generate-event -b=<BundleName>
```

**13-1.指定一个Bundle的asset资源刷新生成asset资源到public下：**
```
php artisan bundle:publish -b=<BundleName>
```

**14-1.指定一个Bundle的lang资源刷新生成lang资源到resources/lang下：**
```
php artisan bundle:publish-lang -b=<BundleName>
```

**15-1.指定一个Bundle的migration资源刷新生成migration资源到databases/migration下：**
```
php artisan bundle:publish-migration -b=<BundleName>
```

**16-1.指定一个Bundle，生成 create 操作的Migration：**
```
php artisan bundle:make-migration create_<TABLE_NAME>_table -b=<BundleName> 
```

**16-2.指定一个Bundle，生成 remove 操作的Migration：**
```
php artisan bundle:make-migration remove_<COLUMN_NAME>_from_<TABLE_NAME>_table -b=<BundleName> 
```

**16-3.指定一个Bundle，生成 add 操作的Migration：**
```
php artisan bundle:make-migration add_<COLUMN_NAME>_to_<TABLE_NAME>_table -b=<BundleName> 
```

**16-4.指定一个Bundle，生成 drop 操作的Migration：**
```
php artisan bundle:make-migration drop_<TABLE_NAME>_table -b=<BundleName>
```

**16-4.create/remove/add/drop...，后带字段参数时，字段之间用逗号 (,) 分隔：**
```
... -f="<COLUMN_NAME>:string, <COLUMN_NAME>:string"
```

**16-5.指定一个Bundle，生成对应操作的Migration，-f (--field)指定字段，多字段之间用 (,) 逗号相隔：**
```
php artisan bundle:make-migration <OPERATION_NAME>_<TABLE_NAME>_table -b=<BundleName>
```

**17-1.指定Bundle生成seeder，默认是生成'TableSeeder'：**
```
php artisan bundle:make-seed <SeedName> -b=<BundleName>
```

**17-2.指定Bundle生成seeder，-d 生成'DatabaseSeeder'：**
```
php artisan bundle:make-seed <SeedName> -b=<BundleName> -d
```

**18-1.指定Bundle，执行migrate：**
```
php artisan bundle:migrate -b=<BundleName>
```

**18-2.指定Bundle，执行migrate，可带与框架自带的一些参数：**
```
php artisan bundle:migrate -b=<BundleName> --database --pretend --force --seed
```

**19-1.指定Bundle，执行seed：**
```
php artisan bundle:seed -b=<BundleName>
```

**19-2.指定Bundle，执行seed，可带与框架自带的一些参数：**
```
php artisan bundle:seed -b=<BundleName> --database --class
```

**20-1.指定Bundle，执行rollback：**
```
php artisan bundle:migrate-rollback -b=<BundleName>
```

**21-1.指定Bundle，执行reset：**
```
php artisan bundle:migrate-reset -b=<BundleName>
```

**22-1.指定Bundle，执行refresh：**
```
php artisan bundle:migrate-refresh -b=<BundleName>
```

**22-3.指定Bundle，执行refresh，与框架一些自带的参数：**
```
php artisan bundle:migrate-refresh -b=<BundleName> --database --force --seed
```
