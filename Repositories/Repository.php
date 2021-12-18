<?php

/**
 * Author: liangwengao
 * Email: wengaolng@gmail.com
 * Date: 2017-01-25
 * Time: 12:57
 */

namespace Awen\Bundles\Repositories;

use Awen\Bundles\Contracts\RepositoryInterface;
use Awen\Bundles\Exceptions\RepositoryException;
use Awen\Bundles\Extensions\RepositoryExtend;
use Illuminate\Foundation\Application;
use Illuminate\Database\Eloquent\Model;

abstract class Repository extends RepositoryExtend implements RepositoryInterface
{
    /**app容器
     * @var Application
     */
    protected $app;

    /**操作的model
     * @var Repository
     */
    protected $model;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->makeModel();
    }

    /**把model添加到容器当中
     * @return Model|mixed
     * @throws RepositoryException
     */
    public function makeModel()
    {
        $class = $this->model();
        if(!class_exists($class)){
            throw new RepositoryException("这个类“ {$class} ”不是类");
        }

        $model = $this->app->make($class);
        if (!$model instanceof Model) {
            throw new RepositoryException("这个类“ {$this->model()} ”不是继承于Illuminate\\Database\\Eloquent\\Model类");
        }

        return $this->model = $model;
    }

    /**重新生成model
     * @throws RepositoryException
     */
    public function resetModel()
    {
        $this->makeModel();
    }

    //-----------------------------//
    //          公共操作
    //-----------------------------//

    /**查找所有数据
     * @param array $columns
     * @return mixed
     */
    public function all($columns = ['*'])
    {
        $results = $this->model->get($columns);
        $this->resetModel();
        return $results;
    }

    /**获取第一条数据
     * @param array $columns
     * @return mixed
     */
    public function first($columns = ['*'])
    {
        $results = $this->model->first($columns);
        $this->resetModel();
        return $results;
    }

    /**检索库的所有数据分页
     * @param int $limit
     * @param array $order_by
     * @param array $where
     * @param array $columns
     * @param string $method
     * @return mixed
     */
    public function paginate($limit = 10, array $order_by = [], array $where = [], $columns = ['*'], $method = "paginate")
    {
        //处理where
        $this->model = $this->treatedWhere($this->model, $where);

        //处理orderBy
        $this->model = $this->treatedOrder($this->model, $order_by);

        $results = $this->model->{$method}($limit, $columns);
        $this->resetModel();
        return $results;
    }

    /**连接表查找数据多条分页数据
     * @param array $where
     * @param array $join
     * @param int $limit
     * @param array $order_by
     * @param array $columns
     * @param string $method
     * @return mixed
     */
    public function whereJoinPaginate(array $where, array $join, $limit = 10, array $order_by = [], $columns = ['*'], $method = "paginate")
    {
        //处理where
        $this->model = $this->treatedWhere($this->model, $where);

        //处理orderBy
        $this->model = $this->treatedOrder($this->model, $order_by);

        //处理join
        $this->model = $this->treatedJoin($this->model, $join);

        //处理page
        $results = $this->model->{$method}($limit, $columns);
        $this->resetModel();
        return $results;
    }


    /**通过ID查找数据
     * @param $id
     * @param array $columns
     * @return mixed
     */
    public function find($id, $columns = ['*'])
    {
        $results = $this->model->find($id, $columns);
        $this->resetModel();
        return $results;
    }

    /**通过字段和值查找多条数据
     * @param $field
     * @param $value
     * @param array $columns
     * @return mixed
     */
    public function findOneByField($field, $value, $columns = ['*'])
    {
        $results = $this->model->where($field, '=', $value)->first($columns);
        $this->resetModel();
        return $results;
    }

    /**通过字段和值查找数据
     * @param $field
     * @param $value
     * @param $limit
     * @param array $columns
     * @param array $order_by = []
     * @return mixed
     */
    public function findByField($field, $value, $limit = null, $columns = ['*'], array $order_by = [])
    {
        //处理orderBy
        $this->model = $this->treatedOrder($this->model, $order_by);

        if ($limit == null)
            $results = $this->model->where($field, '=', $value)->get($columns);
        else
            $results = $this->model->where($field, '=', $value)->take($limit)->get($columns);

        $this->resetModel();
        return $results;
    }

    /**通过条件查找一条
     * @param array $where
     * @param array $columns
     * @param array $order_by
     * @return mixed
     */
    public function findOneWhere(array $where, $columns = ['*'], array $order_by = [])
    {
        //处理where
        $this->model = $this->treatedWhere($this->model, $where);
        $results = $this->model->first($columns);

        //处理orderBy
        $this->model = $this->treatedOrder($this->model, $order_by);

        $this->resetModel();
        return $results;
    }

    /**通过条件查找
     * @param array $where
     * @param array $limit
     * @param array $columns
     * @param array $order_by
     * @return mixed
     */
    public function findWhere(array $where, $limit = null, $columns = ['*'], array $order_by = [])
    {
        //处理where
        $this->model = $this->treatedWhere($this->model, $where);

        //处理orderBy
        $this->model = $this->treatedOrder($this->model, $order_by);

        if ($limit == null)
            $results = $this->model->get($columns);
        else
            $results = $this->model->take($limit)->get($columns);

        $this->resetModel();
        return $results;
    }

    /**通过条件查找一条
     * @param array $where
     * @param array $columns
     * @return mixed
     */
    public function findOneOrWhere(array $where, $columns = ['*'])
    {
        $this->model = $this->treatedOrWhere($this->model, $where);
        $results = $this->model->first($columns);

        $this->resetModel();
        return $results;
    }


    /**通过或者条件查找
     * @param array $where
     * @param array $limit
     * @param array $columns
     * @param array $order_by
     * @return mixed
     */
    public function findOrWhere(array $where, $limit = null, $columns = ['*'], array $order_by = [])
    {
        $this->model = $this->treatedOrWhere($this->model, $where);

        //处理orderBy
        $this->model = $this->treatedOrder($this->model, $order_by);

        if ($limit == null)
            $results = $this->model->get($columns);
        else
            $results = $this->model->take($limit)->get($columns);

        $this->resetModel();
        return $results;
    }



    /**通过一个字段中的多个值查找数据
     * @param $field
     * @param array $values
     * @param int $limit
     * @param array $columns
     * @return mixed
     */
    public function findWhereIn($field, array $values, $limit = null, $columns = ['*'])
    {
        $results = $this->model->whereIn($field, $values)->take($limit)->get($columns);
        $this->resetModel();
        return $results;
    }

    /**通过在一个字段中不包括多个值查找数据
     * @param $field
     * @param array $values
     * @param $limit
     * @param array $columns
     * @return mixed
     */
    public function findWhereNotIn($field, array $values, $limit = null, $columns = ['*'])
    {
        $results = $this->model->whereNotIn($field, $values)->take($limit)->get($columns);
        $this->resetModel();
        return $results;
    }

    /**连接表查找数据多条数据
     * @param array $where
     * @param array $join
     * @param $limit
     * @param array $columns
     * @param array $order_by
     * @return mixed
     */
    public function findWhereJoin(array $where, array $join, $limit = null, $columns = ['*'], array $order_by = [])
    {
        //处理where
        $this->model = $this->treatedWhere($this->model, $where);

        //处理join
        $this->model = $this->treatedJoin($this->model, $join);

        //处理orderBy
        $this->model = $this->treatedOrder($this->model, $order_by);

        if ($limit == null)
            $results = $this->model->get($columns);
        else
            $results = $this->model->take($limit)->get($columns);

        $this->resetModel();
        return $results;
    }

    /**连接表查找数据一条数据
     * @param array $where
     * @param array $join
     * @param array $columns
     * @return mixed
     */
    public function findOneWhereJoin(array $where, array $join, $columns = ['*'])
    {
        //处理where
        $this->model = $this->treatedWhere($this->model, $where);

        //处理join
        $this->model = $this->treatedJoin($this->model, $join);

        $results = $this->model->first($columns);
        $this->resetModel();
        return $results;
    }

    /**连接表查找数据多条数据
     * @param array $where
     * @param array $join
     * @param $limit
     * @param array $columns
     * @param array $order_by
     * @return mixed
     */
    public function findWhereLeftJoin(array $where, array $join, $limit = null, $columns = ['*'], array $order_by = [])
    {
        //处理where
        $this->model = $this->treatedWhere($this->model, $where);

        //处理join
        $this->model = $this->treatedLeftJoin($this->model, $join);

        //处理orderBy
        $this->model = $this->treatedOrder($this->model, $order_by);

        if ($limit == null)
            $results = $this->model->get($columns);
        else
            $results = $this->model->take($limit)->get($columns);

        $this->resetModel();
        return $results;
    }

    /**连接表查找数据一条数据
     * @param array $where
     * @param array $join
     * @param array $columns
     * @return mixed
     */
    public function findOneWhereLeftJoin(array $where, array $join, $columns = ['*'])
    {
        //处理where
        $this->model = $this->treatedWhere($this->model, $where);

        //处理join
        $this->model = $this->treatedLeftJoin($this->model, $join);

        $results = $this->model->first($columns);
        $this->resetModel();
        return $results;
    }

    /**通过一个字段统计总额
     * @param array $where
     * @param $field
     * @return mixed
     */
    public function sumField(array $where, $field)
    {
        //处理where
        $this->model = $this->treatedWhere($this->model, $where);
        $results = $this->model->sum($field);
        $this->resetModel();
        return $results;
    }

    /**通过一个字段统计总额
     * @param array $where
     * @return mixed
     */
    public function countField(array $where = [])
    {
        //处理where
        $this->model = $this->treatedWhere($this->model, $where);
        $results = $this->model->count();
        $this->resetModel();
        return $results;
    }

    /**排序查询
     * @param $column
     * @param string $direction
     * @param null $limit
     * @param array $columns
     * @return mixed
     */
    public function orderBy($column, $direction = 'desc', $limit = null, $columns = ['*']){
        if ($limit == null)
            $results = $this->model->orderBy($column, $direction)->get($columns);
        else
            $results = $this->model->orderBy($column, $direction)->take($limit)->get($columns);

        $this->resetModel();
        return $results;
    }

    /**在数据库中保存一个新的实体
     * @param array $attributes
     * @param bool $object
     * @return bool|mixed
     */
    public function create(array $attributes, $object = false)
    {
        $this->model = $this->model->newInstance($attributes);
        $results = $this->model->save();
        if ($results && $object) {
            $results = $this->model;
        }
        $this->resetModel();
        return $results;
    }

    /**通过ID更新一个实体库
     * @param array $attributes
     * @param $id
     * @return bool
     */
    public function update(array $attributes, $id)
    {
        $model = $this->model->find($id);
        $model->fill($attributes);
        $results = $model->save();

        $this->resetModel();
        return $results;
    }

    /**通过一个字段更新一个实体库
     * @param array $attributes
     * @param $field
     * @param $value
     * @return bool
     */
    public function updateByField(array $attributes, $field, $value)
    {
        $model = $this->model->where($field, '=', $value)->first();
        $model->fill($attributes);
        $results = $model->save();

        $this->resetModel();
        return $results;
    }

    /**通过ID删除一个实体库
     * @param $id
     * @return bool
     */
    public function delete($id)
    {
        $results = $this->find($id)->delete();
        $this->resetModel();
        return $results;
    }

    /**通过条件删除一个实体库
     * @param array $where
     * @return bool
     */
    public function whereDelete(array $where = [])
    {
        //处理where
        $this->model = $this->treatedWhere($this->model, $where);
        $results = $this->model->delete();
        $this->resetModel();
        return $results;
    }
}