<?php


namespace QPlayer\Cache;

use Database__Interface;
use Exception;

class Database extends Cache
{
    /**
     * @var Database__Interface
     */
    protected $db;
    protected $table;

    public function __construct()
    {
        global $zbp;
        $this->db = $zbp->db;
        $this->table = $this->db->dbpre . 'QPlayer2';
    }

    public function set($key, $data, $expire = 86400)
    {
        $this->db->Query($this->db->sql->get()->insert($this->table)->data(array(
            'mKey' => md5($key),
            'mData' => $data,
            'mTime' => time() + $expire
        ))->sql);
    }

    public function get($key)
    {
        $db = $this->db;
        // 回收过期数据
        $this->db->Query($this->db->sql->get()->delete($this->table)->where('<=', 'mTime', time())->sql);

        $r = $db->Query($db->sql->get()->selectany('mData')->from($this->table)->where('=', 'mKey', md5($key))->sql);
        return @$r[0]['mData'];
    }

    /**
     * @throws Exception
     */
    public function install()
    {
        $type = $this->db->type;
        switch (true) {
            case false !== stripos($type, 'Mysql'):
                $adapter = 'MySQL';
                break;
            case false !== stripos($type, 'Pgsql'):
            case false !== stripos($type, 'PostgreSQL'):
                $adapter = 'PgSQL';
                break;
            case false !== stripos($type, 'SQLite'):
                $adapter = 'SQLite';
                break;
            default:
                throw new Exception('Unknown db type: ' . $type);
        }
        $sql = file_get_contents(__DIR__ . "/$adapter.sql");
        $this->db->Query(str_replace('%table%', $this->table, $sql));
    }

    public function uninstall()
    {
        $this->db->DelTable($this->table);
    }

    public function flush()
    {
        $this->db->Query($this->db->sql->get()->truncate($this->table)->sql);
    }
}