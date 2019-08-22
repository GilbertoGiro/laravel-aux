<?php

namespace LaravelAux;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * BaseRepository constructor.
     *
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Method to get all Model Objects
     *
     * @return \Illuminate\Database\Eloquent\Collection|Model[]
     */
    public function all()
    {
        return $this->model->all();
    }

    /**
     * Method to get Model Object by id
     *
     * @param integer $id
     * @return mixed
     */
    public function find(int $id)
    {
        return $this->model->find($id);
    }

    /**
     * Method to get Model Object by passed Params
     *
     * @param array $data
     * @return mixed
     */
    public function findBy(array $data)
    {
        $return = $this->model->all();
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $return = $return->whereIn($key, $value);
                continue;
            }
            $return = $return->where($key, $value);
        }
        return $return->first();
    }

    /**
     * Method to get Model Objects by passed between dates
     *
     * @param string $key
     * @param array $value
     * @return mixed
     */
    public function whereBetween(string $key, array $value)
    {
        return $this->model::whereBetween($key, $value);
    }

    /**
     * Method to get Model Object with Relations
     *
     * @param array $relations
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Builder|Model|null|object
     */
    public function with(array $relations, int $id = null)
    {
        if ($id) {
            return $this->model::with($relations)->where('id', $id)->first();
        }
        return $this->model::with($relations);
    }

    /**
     * Method to get passed Model Objects Attributes
     *
     * @param string $columns
     * @return mixed
     */
    public function select($columns = '*')
    {
        return $this->model->select($columns);
    }

    /**
     * Method to Create Model Object
     *
     * @param array $data
     * @return mixed
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Method to Get the first occurrence or Create an Model Object if that doesn't exists
     * Obs. The difference between that method and FirstOrNew, it's because in that method, eloquent persist the information
     * on database.
     *
     * @param array $condition
     * @param array $data
     * @return mixed
     */
    public function firstOrCreate(array $condition, array $data)
    {
        return $this->model->firstOrCreate($condition, $data);
    }

    /**
     * Method to Get the first occurrence or Create an Model Object if that doesn't exists
     * Obs. The difference between that method and FirstOrCreate, it's because in that method, eloquent doesn't persist the information
     * on database.
     *
     * @param array $condition
     * @param array $data
     * @return mixed
     */
    public function firstOrNew(array $condition, array $data)
    {
        return $this->model->firstOrNew($condition, $data);
    }

    /**
     * Method to update Model Object
     *
     * @param array $data
     * @param int $id
     * @return mixed
     */
    public function update(array $data, int $id)
    {
        return $this->model->find($id)->update($data);
    }

    /**
     * Method to delete Model Object
     *
     * @param int $id
     * @return mixed
     */
    public function delete(int $id)
    {
        return $this->model->find($id)->delete();
    }

    /**
     * Method to get Model Objects count
     *
     * @return mixed
     */
    public function count()
    {
        return $this->model->count();
    }

    /**
     * Method to get Model Object Attributes
     *
     * @return array
     */
    public function getGuarded()
    {
        return $this->model->getGuarded();
    }

    /**
     * Method to get Model Object Attributes
     *
     * @return array
     */
    public function getFillable()
    {
        return $this->model->getFillable();
    }

    /**
     * Method to get Model Table
     *
     * @return string
     */
    public function getTable()
    {
        return $this->model->getTable();
    }

    /**
     * Method to Eager Load if Relation exists
     *
     * @param $query
     * @param $relations
     * @return mixed
     * @throws \ReflectionException
     */
    public function withRelationIfExists($query, $relations)
    {
        $request = request();
        $reflection = new \ReflectionClass($this->model);
        $allRelations = array_column($reflection->getMethods(), 'name');

        if (is_array($relations)) {
            foreach ($relations as $relation) {
                $conditions = $request->get(str_replace('.', '_', $relation));
                if (!in_array($relation, $allRelations) && !is_int(strpos($relation, '.'))) {
                    continue;
                }
                if (!$conditions) {
                    $query = $query->with($relation);
                    continue;
                }
                $query = $query->with($relation)->whereHas($relation, function ($query) use ($conditions, $relation) {
                    foreach ($conditions as $column => $condition) {
                        if (is_array($condition)) {
                            foreach ($condition as $value) {
                                $query = $this->childrenWhere($query, $column, $value, $relation);
                            }
                            continue;
                        }
                        $query = $this->childrenWhere($query, $column, $condition, $relation);
                    }
                });
            }
        } else {
            if (!in_array($relations, $allRelations)) {
                return $query;
            }
            $conditions = $request->get($relations);
            if (!$conditions) {
                $query = $query->with($relations);
                return $query;
            }
            $query = $query->with($relations)->whereHas($relations, function ($query) use ($conditions) {
                foreach ($conditions as $column => $condition) {
                    $query = $this->childrenWhere($query, $column, $condition);
                }
            });
        }
        return $query;
    }

    /**
     * Method to Eager Load if Relation exists and isn't empty
     *
     * @param $query
     * @param $relations
     * @return mixed
     * @throws \ReflectionException
     */
    public function withRelationIfNotEmpty($query, $relations)
    {
        $request = request();
        $reflection = new \ReflectionClass($this->model);
        $allRelations = array_column($reflection->getMethods(), 'name');

        if (is_array($relations)) {
            foreach ($relations as $relation) {
                $conditions = $request->get($relation);
                if (!in_array($relation, $allRelations) && !is_int(strpos($relation, '.'))) {
                    continue;
                }

                if (!$conditions) {
                    $query = $query->has($relation)->with($relation);
                    continue;
                }

                $query = $query->has($relation)->with($relation)->whereHas($relation, function ($query) use ($conditions) {
                    foreach ($conditions as $column => $condition) {
                        if (is_array($condition)) {
                            foreach ($condition as $value) {
                                $query->where($column, $value);
                            }
                            continue;
                        }

                        $query->where($column, $condition);
                    }
                });
            }
        } else {
            if (!in_array($relations, $allRelations)) {
                return $query;
            }

            $conditions = $request->get($relations);
            if (!$conditions) {
                $query = $query->has($relations)->with($relations);

                return $query;
            }
            $query = $query->has($relations)->with($relations)->whereHas($relations, function ($query) use ($conditions) {
                foreach ($conditions as $column => $condition) {
                    $query->where($column, $condition);
                }
            });
        }
        return $query;
    }

    /**
     * Method to get rows who doesn't have relation (It's empty)
     *
     * @param $query
     * @param $relations
     * @return mixed
     * @throws \ReflectionException
     */
    public function withRelationEmpty($query, $relations)
    {
        $reflection = new \ReflectionClass($this->model);
        $allRelations = array_column($reflection->getMethods(), 'name');

        if (is_array($relations)) {
            foreach ($relations as $relation) {
                if (!in_array($relation, $allRelations) && !is_int(strpos($relation, '.'))) {
                    continue;
                }
                $query = $query->doesnthave($relation);
            }
        } else {
            if (!in_array($relations, $allRelations)) {
                return $query;
            }
            $query = $query->doesnthave($relations);
        }
        return $query;
    }

    /**
     * Method to filter in Children Table - That method is valid only in Abstract Repository (withRelationIfExists)
     *
     * @param $query
     * @param $key
     * @param $value
     * @return mixed
     */
    private function childrenWhere($query, $key, $value, $relation = null)
    {
        $query->where(function ($subquery) use ($query, $key, $value, $relation) {
            if (is_array($value)) {
                foreach ($value as $column => $condition) {
                    $subquery->whereRaw("LOWER({$key}) LIKE LOWER(?)", '%' . $condition . '%');
                }
                return $query;
            }

            if($relation){

                try{
                    $relationModel = $this->model->{$relation}(); // Returns a Relations subclass like BelongsTo or HasOne.
                    $relatedModel = $relationModel->getRelated(); // Returns a new empty Model
                    $tableName = $relatedModel->getTable();

                    $type = DB::connection()->getDoctrineColumn($tableName, $key)->getType()->getName();

                } catch(Exception $e){
                    $type = null;
                }

                if(isset($type) && $type == 'integer'){
                    $subquery->whereRaw($key, $value);
                } else {
                    $subquery->whereRaw("LOWER({$key}) LIKE LOWER(?)", '%' . $value . '%');
                }
            }
        });
        return $query;
    }
}
