<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
class BaseController extends \App\Http\Controllers\Controller{

    /**
     *
     * @var \App\Repositories\BaseRepository
     */
    public $repository;


    public function create() {

    }

    public function destroy($id) {
        if($this->repository->destroy($id))
        {
            return $this->success("Succcessfully Deleted", []);
        }
        return $this->error("Unable to find the required record.");
    }

    public function edit($id) {

    }

    public function index(Request $request) {
        $data = $this->repository->getAll();
        if(empty($data))
        {
            return $this->success("No Data Found", []);
        }
        return $this->success("Date Returned", $data->toArray());
    }

    public function show($id) {
        $data = $this->repository->get($id);

        if(empty($data->toArray()))
        {
            return $this->error("No Record Found.");
        }
        return $this->success("Record Retrieved.", $data);
    }

    public function store(Request $request) {
        $data = $request->all();
        if(!$this->repository->store($data))
        {
            return $this->response(405, "Validation Errors", $this->repository->getErrors()->toArray(), 200);
        }
        return $this->success("Saved Successfully.", $this->repository->getStoredObject());
    }

    public function update(Request $request,$id) {
        $data = $request->all();
        if(!$this->repository->update($id, $data))
        {
            return $this->response(405, "Validation Errors", $this->repository->getErrors(), 200);
        }
        return $this->success('Updated Successfully.', $this->repository->getStoredObject());
    }

    public function error($message) {
        return $this->response(404, $message, [], 404);
    }

    public function response($statusCode, $message, $data = [], $httpCode = 200) {
        return response(['code' => $statusCode, 'message' => $message, 'data'=>$data], $httpCode);
    }

    public function success($message, $data) {
        return $this->response(200, $message, $data, 200);
    }

}