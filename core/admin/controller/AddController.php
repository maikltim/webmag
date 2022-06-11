<?php 


namespace core\admin\controller;


class AddController extends BaseAdmin
{

    protected function inputData() {

        if(!$this->userId) $this->exectBase();

        $this->createTableData();

        $this->createOutputData();

    }


}