<?php

namespace DklCreations\SHCommon\Services;

abstract class BaseService
{

    /**
     * @var null
     */
    public $model = null;

    /**
     * Create a new service instance.
     *
     * @return void
     */
    public function __construct()
    {
        // inject our services as class properties
        if ( isset($this->services) && is_array($this->services) ) {
            foreach ($this->services as $property => $class) {
                $this->{$property} = new $class;
            }
        }
    }

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
        if ($this->model == null) {
            fail('No model record has been loaded yet.');
        }
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
        if (preg_match('/DklCreations/', $service)) {
            $model = str_replace('DklCreations\SHCommon\Services\\', '', $service);
            $model_name = 'DklCreations\SHCommon\Models\\' . str_replace('Service', '', $model);
        } else {
            $model = str_replace('App\Services\\', '', $service);
            $model_name = 'App\Models\\' . str_replace('Service', '', $model);
        }
        return $model_name;
    }

}
