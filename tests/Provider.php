<?php
class Provider
{
    protected $config = array();

    public function __construct()
    {
        $this->config = array(
            'data' => array(),
            'provided' => false
        );
    }

    public function isProvided()
    {
        return $this->config['provided'];
    }

    protected function setProvided()
    {
        $this->config['provided'] = true;
    }

    protected function getData()
    {
        return $this->config['data'];
    }

    protected function addData($data)
    {
        $this->config['data'][] = $data;
    }

    /**
     * @param null $data
     * @return $this|mixed
     */
    public function provide($data = null)
    {
        if($this->isProvided()) {
            return $this;
        }

        if (null === $data) {
            $this->setProvided();
            return $this->getData();
        }

        $this->addData($data);

        return $this;
    }

    public function endProvide()
    {
        $data = $this->provide();

        return ($this === $data) ? null : $data;
    }
}