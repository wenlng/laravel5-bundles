<?php

/**
 * Author: liangwengao
 * Email: 871024608@qq.com
 * Date: 2016-01-26
 * Time: 18:25
 */

namespace Awen\Bundles\Extensions;

use Awen\Bundles\Exceptions\ExtensionException;

/**仓库扩展类
 * Class RepositoryExtend
 * @package Awen\Bundles\Extensions
 */
class RepositoryExtend
{
    /**处理where
     * @param $model
     * @param array $where
     * @return mixed
     * @throws ExtensionException
     */
    protected function treatedWhere($model, array $where)
    {
        try {
            foreach ($where as $field => $value) {
                if (is_array($value)) {
                    list($field, $condition, $val) = $value;
                    $model = $model->where($field, $condition, $val);

                } else {
                    $model = $model->where($field, '=', $value);
                }
            }

        } catch (\Exception $e) {
            throw new ExtensionException("处理where时出错了，请检查参数！");
        }

        return $model;
    }

    /**处理Orwhere
     * @param $model
     * @param array $where
     * @return mixed
     * @throws ExtensionException
     */
    protected function treatedOrWhere($model, array $where)
    {
        try {
            $index = 0;
            foreach ($where as $field => $value) {
                if ($index == 0) {
                    if (is_array($value)) {
                        list($field, $condition, $val) = $value;
                        $model = $model->where($field, $condition, $val);
                    } else {
                        $model = $model->where($field, '=', $value);
                    }
                } else {
                    if (is_array($value)) {
                        list($field, $condition, $val) = $value;
                        $model = $model->orwhere($field, $condition, $val);
                    } else {
                        $model = $model->orwhere($field, '=', $value);
                    }
                }
                $index++;

            }
        } catch (\Exception $e) {
            throw new ExtensionException("处理where时出错了，请检查参数！");
        }

        return $model;
    }

    /**处理orderBy
     * @param $model
     * @param array $order_by
     * @return mixed
     * @throws ExtensionException
     */
    protected function treatedOrder($model, array $order_by)
    {
        try {
            foreach ($order_by as $order_key => $order_value) {
                if (is_array($order_value)) {
                    list($ok, $ov) = $order_value;
                    $model = $model->orderBy($ok, $ov);
                } else {
                    $model = $model->orderBy($order_key, $order_value);
                }
            }
        } catch (\Exception $e) {
            throw new ExtensionException("处理order_by时出错了，请检查参数！");
        }

        return $model;
    }

    /**处理join
     * @param $model
     * @param array $join
     * @return mixed
     * @throws ExtensionException
     */
    protected function treatedJoin($model, array $join)
    {
        try {
            foreach ($join as $field => $value) {
                if (!is_array($value)) {
                    list($table, $left_val, $condition, $right_val) = $join;
                    $model = $model->join($table, $left_val, $condition, $right_val);
                    break;
                } else {
                    list($table, $left_val, $condition, $right_val) = $value;
                    $model = $model->join($table, $left_val, $condition, $right_val);
                }
            }
        } catch (\Exception $e) {
            throw new ExtensionException("处理join时出错了，请检查参数！");
        }

        return $model;
    }

    /**处理 $between
     * @param $model
     * @param array $between
     * @return mixed
     * @throws ExtensionException
     */
    protected function treatedBetween($model, array $between)
    {
        try {
            foreach ($between as $key => $value) {
                if (is_array($value)) {
                    list($field, $condition) = $value;
                    $model = $model->whereBetween($field, $condition);

                } else {
                    $model = $model->whereBetween($key, $value);
                }
            }
        } catch (\Exception $e) {
            throw new ExtensionException("处理between时出错了，请检查参数！");
        }

        return $model;
    }

}