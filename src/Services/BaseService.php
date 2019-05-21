<?php

namespace Lewisqic\SHCommon\Services;

abstract class BaseService
{

    /**
     * @var null
     */
    public $model = null;


    /**
     * Load an existing user record
     *
     * @param  array  $id
     * @return object
     */
    public function load($id)
    {
        $model_name = $this->getModelName();
        $this->model = $model_name::findOrFail($id);
        return $this;
    }


    /**
     * create a new record
     *
     * @param  array  $data
     * @return object
     */
    public function create($data)
    {
        $model_name = $this->getModelName();
        $record = $model_name::create($data);
        return $record;
    }


    /**
     * update a record
     *
     * @param  array  $data
     * @return object
     */
    public function update($data)
    {
        $this->model->fill($data)->save();
        return $this->model;
    }

    /**
     * delete a record
     *
     * @param  int  $id
     * @return object
     */
    public function delete($id)
    {
        $model_name = $this->getModelName();
        $record = $model_name::findOrFail($id);
        $record->delete();
        return $record;
    }


    /**
     * restore a record
     *
     * @param  int  $id
     * @return object
     */
    public function restore($id)
    {
        $model_name = $this->getModelName();
        $record = $model_name::withTrashed()->findOrFail($id);
        $record->restore();
        return $record;
    }

    /**
     * determine our model name
     *
     * @return mixed|string
     */
    protected function getModelName()
    {
        $service = get_called_class();
        $model = str_replace('App\Services\\', '', $service);
        $model_name = 'App\Models\\' . str_replace('Service', '', $model);
        return $model_name;
    }

}
