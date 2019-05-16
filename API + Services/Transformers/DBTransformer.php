<?php

namespace App\Transformers;

class DBTransformer
{
    /**
     * @var array
     */
    protected $result;

    /**
     * DBTransformer constructor.
     * @param array $result
     */
    public function __construct(array $result)
    {
        $this->result = $result;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $res = [];

        foreach ($this->result as $ar) {
            $res[] = (array) $ar;
        }

        return $res;
    }

    /**
     * @return array
     */
    public function toStdArray()
    {
        $res = [];

        foreach ($this->result as $ar) {
            $res[] = (object) $ar;
        }

        return $res;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getArrayCollection()
    {
        return collect($this->toArray());
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getStdClassCollection()
    {
        return collect($this->result);
    }
}
