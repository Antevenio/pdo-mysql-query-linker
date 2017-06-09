<?php
namespace PdoMysqlQueryLinker\TableBuilder;

class Type {
    protected $name;
    protected $useLength = false;
    protected $usePrecision = false;

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $type
     * @return Type
     */
    public function setName($type)
    {
        $this->name = $type;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUseLength()
    {
        return $this->useLength;
    }

    /**
     * @param mixed $useLength
     * @return Type
     */
    public function setUseLength()
    {
        $this->useLength = true;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUsePrecision()
    {
        return $this->usePrecision;
    }

    /**
     * @param mixed $usePrecision
     * @return Type
     */
    public function setUsePrecision()
    {
        $this->usePrecision = true;

        return $this;
    }

    public function getSql($columnMeta)
    {
        $ret = $this->getName();
        if (!$this->getUseLength() && !$this->getUsePrecision()) {
            return $ret;
        }

        $ret .= "(";
        if ($this->getUseLength()) {
            $ret .= $columnMeta["len"];
        }
        if ($this->getUsePrecision()) {
            $ret .= ",".  $columnMeta["precision"];
        }
        $ret .= ")";

        return $ret;
    }
}