<?php
namespace App\Repositories;


class BaseRepository implements \App\Repositories\BaseRepositoryInterface{

    protected $model;
    protected $withParams = [];
    protected $paginate = false;
    protected $limit = 10;
    protected $where = [
        'get' => []
    ];
    protected $rules = [];
    protected $validator;
    protected $storedObject;
    protected $fields= ['*'];
    public $orderByParams = ['id', "DESC"];

    public function model($model)
    {
        $this->model = $model;
        return $this;
    }

    public function rules($rules){
        $this->rules  = $rules;
        return $this;
    }

    public function fields($fields)
    {
        $this->fields = $fields;
        return $this;
    }

    public function getErrors()
    {
        return (isset($this->validator)?$this->validator->messages():[]);
    }

    public function with($params){
        $this->withParams = $params;
        return $this;
    }

    public function paginate($state = true, $limit=10){
        $this->paginate = $state;
        $this->limit = $limit;
        return $this;
    }


    public function where($data, $type="get"){
        if(array_key_exists($type, $this->where))
        {
            $this->where[$type][] = $data;
        }else{
            $this->where[$type] = [$data];
        }
        return $this;
    }

    public function prepareQuery(){
        $query = $this->model->select($this->fields)
            ->with($this->withParams)
            ->orderBy($this->orderByParams[0], $this->orderByParams[1]);
        foreach($this->where['get'] as $key => $value)
        {
            $query->where($value[0], $value[1], $value[2]);
        }
        return $query;
    }

    public function destroy($id) {
        $obj = $this->model->find($id);
        if(empty($obj))
            return false;
        $obj->delete();
        return true;
    }

    public function get($id) {
        return $this->where(['id', '=', $id])->prepareQuery()->first();
    }

    public function getAll($limit = -1) {
        $data = [];
        if($limit == -1){
            if($this->paginate)
            {
                $data = $this->prepareQuery()->paginate($this->limit);

            }else{
                $data = $this->prepareQuery()->get();
            }
        }else{
            $data = $this->prepareQuery()->take($limit)->get();
        }
        return $data;
    }

    public function validateInput($data)
    {
        $this->validator = \Validator::make($data, $this->rules);
        if($this->validator->fails())
        {
            return false;
        }
        return true;
    }

    public function store($data) {
//        htmlspecialchars
        $dataArray = [];
        foreach ($data as $key => $value) {
            if(is_object($value) || is_array($value))
            {
                $dataArray[$key] = $value;
            }else{
                $dataArray[$key] = strip_tags($value, '<div><p><a><br><span>');
            }
        }

        $data = $dataArray;
        if(key_exists("password", $data))
        {
            $data['password'] = \Hash::make($data['password']);
        }
//        $this->validator = \Validator::make($data, $this->rules);
//        if($this->validator->fails())
//        {
//            return false;
//        }
        if(!$this->validateInput($data))
        {
            return false;
        }

        foreach($data as $key => $value)
        {
            if(is_object($value) || is_array($value))
            {
                $data[$key] = json_encode($value);
            }
        }

        if(array_key_exists("user_id", $this->rules) && !isset($data['user_id']))
        {
            $data['user_id'] = \Auth::id();
        }
        $this->storedObject = new $this->model($data);
        $this->storedObject->save();
        return true;
    }

    public function getStoredObject()
    {
        return $this->storedObject;
    }

    public function update($id, $data) {
        $this->storedObject = $this->model->find($id);
        $rules = [];
        foreach ($data as $key => $value) {

            if(is_object($value) || is_array($value))
            {
                $data[$key] = json_encode($value);
            }else{
                $data[$key] = strip_tags($value, '<div><p><a><br><span>');
            }

            $this->storedObject->$key = $data[$key];
            if(array_key_exists($key, $this->rules))
            {
                $rules[$key] = $this->rules[$key];
            }
        }

        $this->validator = \Validator::make($data, $rules);
        if($this->validator->fails())
        {
            return false;
        }
        $this->storedObject->save();
        return true;
    }


    public function __log($type, $data, $syncState = "no", $syncID = "")
    {
        return null;
    }

}