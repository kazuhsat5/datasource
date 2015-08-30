<?php

/**
 * TSV File Datasource
 *
 * @author kazuhsat <kazuhsat555@gmail.com>
 * @copyright Copyright (C) 2015 kazuhsat. All Rights Reserved.
 * @link
 */

App::uses('DataSource', 'Model/DataSource');

/**
 * TSV File Datasource class
 */
class TsvSource extends DataSource {

/**
 * The description of Datasource
 *
 * @var string
 */
    public $description = 'TSV File Source';

/**
 * The configuration of Datasource
 *
 * @var array
 */
    public $config = array(
        'root'   => '/tmp/csv',
        'header' => true 
    );

/**
 * Constructor
 *
 * @param array Array of configuration information of the Datasource.
 */
    public function __construct($config = null, $autoConnect = true) {
        parent::__construct($config);
    }

/**
 * Caches/returns cached results
 *
 * @param mixed Unused int this class.
 * @return null
 */
    public function listSources($data = null) {
        return null;
    }

/**
 * Return a Model description (metadata)
 *
 * @param Model $model The model to describe.
 * @return null|array
 */
    public function describe($model) {
        if (!$this->config['header']) return null;

        $path = sprintf('%s/%s.csv', $this->config['root'], $model->table);
        $fp = fopen($path, "r");
        $line = fgets($fp);
        fclose($fp);

        return explode("\t", $line);
    }

/**
 * Read records from the Datasource.
 *
 * @param Model $model The model being read.
 * @param array $queryData An array of query data used to find the data.
 * @param int $recursive Number of levels of association.
 * @return mixed
 */
    public function read(Model $model, $queryData = array(), $recursive = null) {
        $path = sprintf('%s/%s.csv', $this->config['root'], $model->table);

        $results = array();
        foreach (file($path) as $lk => $lv) {
            if ($this->config['header'] && $lk === 0) {
                $header = $lv;

                continue;
            }

            if ($this->config['header']) {
                $fields = explode("\t", $lv);

                $result = array();
                foreach ($fields as $fk => $fv) {
                    $result[$header[$fk]] = $fv;
                }

                $results[] = $result;
            } else {
                $results[] = explode("\t", $lv);
            }
        }

        if (!empty($queryData['fields']['count'])) {
            return array(
                array(
                    $model->name => array(
                        'count' => count($results)
                    )
                )
            );
        }

        $start = 0;
        if (is_null($queryData['limit'] * ($queryData['page'] - 1))) {
            $start = $queryData['limit'] * ($queryData['page'] - 1);
        }

        return array(
            $model->name => array_slice($results, $start, $queryData['limit'])
        );
    }
}
