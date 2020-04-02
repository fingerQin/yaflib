<?php
/**
 * 将SESSION封装到MySQL中保存。
 * --------------------------
 * CREATE TABLE ms_session (
 *   session_id varchar(100) NOT NULL COMMENT 'php session_id',
 *   session_expire int(11) UNSIGNED NOT NULL COMMENT 'session到期时间',
 *   session_data blob,
 *   UNIQUE KEY `session_id` (`session_id`)
 * )ENGINE = MyISAM DEFAULT CHARSET=utf8 COMMENT 'session表';
 * --------------------------
 * @author fingerQin
 * @date 2016-09-11
 */

namespace finger\Session\Mysql;

use finger\Exception\SessionException;

class SessionHandler implements \SessionHandlerInterface
{
    /**
     * mysql对象。
     *
     * @var Client
     */
    protected $_client;

    /**
     * session前缀。
     *
     * @var string
     */
    protected $_prefix = 'sess_';

    /**
     * session有效期。
     *
     * @var int
     */
    protected $_ttl;

    /**
     *
     * @var array
     */
    protected $_cache = [];

    /**
     * 构造方法。
     *
     * @param PDO    $pdo MySQL连接对象。
     * @param int    $ttl
     * @param string $prefix
     * @throws \Exception
     */
    public function __construct($pdo, $ttl = null, $prefix = 'sess_')
    {
        $this->_ttl    = $ttl ?  : ini_get('session.gc_maxlifetime');
        $this->_client = $pdo;
        $this->_prefix = $prefix;
    }

    /**
     * 关闭当前 session。
     *
     * @return bool
     */
    public function close()
    {
        return true;
    }

    /**
     * 清除 session。
     * @param  string  $sessionId
     * @return bool
     */
    public function destroy($sessionId)
    {
        $sql = 'DELETE FROM ms_session WHERE session_id = :session_id';
        $sessionId = $this->_prefix . $sessionId;
        $sth = $this->_client->prepare($sql);
        $sth->bindParam(':session_id', $sessionId, \PDO::PARAM_STR);
        $sth->execute();
        return true;
    }

    /**
     * session 垃圾回收。
     * @param  int  $maxlifetime
     * @return bool
     */
    public function gc($maxlifetime)
    {
        $sql = 'DELETE FROM ms_session WHERE session_expire < :session_expire';
        $sth = $this->_client->prepare($sql);
        $sth->bindParam(':session_expire', $maxlifetime, \PDO::PARAM_INT);
        try {
            $sth->execute();
        } catch (\Exception $e) {
            throw new SessionException("\finger\session\mysql\SessionHandler::gc method is wrong");
        }
        return true;
    }

    /**
     *
     * @param  string  $savePath
     * @param  string  $name
     * @return bool
     */
    public function open($savePath, $name)
    {
        return true;
    }

    /**
     * 读取 session。
     *
     * @param  string  $sessionId
     * @return string
     */
    public function read($sessionId)
    {
        $realSessionId = $this->_prefix . $sessionId;
        if (isset($this->_cache[$realSessionId])) {
            return $this->_cache[$realSessionId];
        }
        $sql = 'SELECT * FROM ms_session WHERE session_id = :session_id LIMIT 1';
        $sth = $this->_client->prepare($sql);
        $sth->bindParam(':session_id', $realSessionId, \PDO::PARAM_STR);
        try {
            $sth->execute();
            $result = $sth->fetch(\PDO::FETCH_ASSOC);
            if ($result) {
                if ($result['session_expire'] < time()) {
                    $this->destroy($sessionId);
                    return ''; // session 已经过期。
                }
                $sessData = json_decode($result['session_data'], true);
                $sessData = $sessData === null ? '' : $sessData;
                $this->_cache[$realSessionId] = $sessData;
                return $sessData;
            } else {
                return '';
            }
        } catch (\Exception $e) {
            throw new SessionException("服务器繁忙");
        }
    }

    /**
     * 写session。
     *
     * @param  string  $sessionId
     * @param  string  $sessionData
     * @return bool
     */
    public function write($sessionId, $sessionData)
    {
        $realSessionId = $this->_prefix . $sessionId;
        $this->_cache[$realSessionId] = $sessionData;
        $sessionDataJson = json_encode($sessionData);
        $ttl = $this->_ttl + time();
        $sql = 'REPLACE INTO ms_session(session_id, session_expire, session_data) VALUES(:session_id, :session_expire, :session_data)';
        $sth = $this->_client->prepare($sql);
        $sth->bindParam(':session_id', $realSessionId, \PDO::PARAM_STR);
        $sth->bindParam(':session_expire', $ttl, \PDO::PARAM_INT);
        $sth->bindParam(':session_data', $sessionDataJson, \PDO::PARAM_STR);
        $sth->execute();
        return true;
    }
}