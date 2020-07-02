<?php declare(strict_types=1);

namespace Application\SSO;

use Application\Zimbra\PreAuth;
use Laminas\Db\Adapter\AdapterInterface as Adapter;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Db\TableGateway\Feature\RowGatewayFeature;
use Mezzio\Session\SessionInterface;
use Psr\Log\LoggerInterface as Logger;

/**
 * Base authentication class
 */
abstract class BaseAuthentication implements AuthenticationInterface
{
    protected $adapter;
    protected $logger;

    protected $protocol;
    protected $userName;
    protected $uidMapping = 'uid';

    public function __construct(Adapter $adapter, Logger $logger)
    {
        $this->adapter = $adapter;
        $this->logger = $logger;
    }

    public function getUserName(string $sessionId = NULL): string
    {
        if (!empty($sessionId)) {
            $hashedSessionId = hash('sha256', $sessionId);
            $table = new TableGateway('sso_login', $this->adapter, new RowGatewayFeature('id'));
            $rowset = $table->select([
                'session_id' => $hashedSessionId,
                'protocol' => $this->protocol,
            ]);
            if ($rowset->count()) {
                $row = $rowset->current();
                $this->userName = $row['user_name'];
            }
        }
        return $this->userName;
    }

    public function getUidMapping(): string
    {
        return $this->uidMapping;
    }

    public function setUidMapping($uidMapping): void
    {
        $this->uidMapping = trim($uidMapping);
    }

    protected function saveSsoLogin($sessionId, array $data = []): void
    {
        $hashedSessionId = hash('sha256', $sessionId);
        $table = new TableGateway('sso_login', $this->adapter, new RowGatewayFeature('id'));
        $rowset = $table->select([
            'session_id' => $hashedSessionId,
            'protocol' => $this->protocol,
        ]);
        if ($rowset->count() == 0 && !empty($this->userName)) {
            $this->logger->debug('save sso session login for {user_name} with id: {session_id}', [
                'user_name' => $this->userName,
                'session_id' => $hashedSessionId,
            ]);
            $insert = [
                'user_name' => $this->userName,
                'session_id' => $hashedSessionId,
                'protocol' => $this->protocol,
                'ip' => self::remoteIp(),
                'created' => time(),
            ];
            if (!empty($data)) {
                $insert['data'] = json_encode($data);
            }
            $table->insert($insert);
        }
    }

    protected function saveSsoLogout($sessionId)
    {
        $hashedSessionId = hash('sha256', $sessionId);
        $table = new TableGateway('sso_login', $this->adapter, new RowGatewayFeature('id'));
        $rowset = $table->select([
            'session_id' => $hashedSessionId,
            'protocol' => $this->protocol,
        ]);
        if ($rowset->count()) {
            $this->logger->debug('save sso session logout for {user_name} with id: {session_id}', [
                'user_name' => $row['user_name'],
                'session_id' => $hashedSessionId,
            ]);
            $row = $rowset->current();
            $row['logout_time'] = time();
            $row->save();
        }
    }

    protected static function remoteIp(): string
    {
        static $remoteIp;
        if (empty($remoteIp)) {
            if (isset($_SERVER)) {
                if (isset($_SERVER['HTTP_CLIENT_IP'])) {
                    $remoteIp = $_SERVER['HTTP_CLIENT_IP'];
                }
                elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
                    $remoteIp = $_SERVER['HTTP_FORWARDED_FOR'];
                }
                elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                    $remoteIp = $_SERVER['HTTP_X_FORWARDED_FOR'];
                }
                else {
                    $remoteIp = $_SERVER['REMOTE_ADDR'];
                }
            }
            else {
                if (getenv('HTTP_CLIENT_IP')) {
                    $remoteIp = getenv('HTTP_CLIENT_IP');
                }
                elseif (getenv('HTTP_FORWARDED_FOR')) {
                    $remoteIp = getenv('HTTP_FORWARDED_FOR');
                }
                elseif (getenv('HTTP_X_FORWARDED_FOR')) {
                    $remoteIp = getenv('HTTP_X_FORWARDED_FOR');
                }
                else {
                    $remoteIp = getenv('REMOTE_ADDR');
                }
            }
        }
        return $remoteIp;
    }
}