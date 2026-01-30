<?php

namespace services\API;

use PHPUnit\Framework\TestCase;

class MockLogger {
	const LOG_ERROR = 1;
	const LOG_WARNING = 2;
	public function log($message, $level = 1) {}
}

class MockUser {
	public int $id;
	public string $username;
	public string $source = 'ils';
	private array $permissions;
	
	public function __construct(array $permissions = []) {
		$this->id = rand(1000, 9999);
		$this->username = 'test_user_' . $this->id;
		$this->permissions = $permissions;
	}
	
	public function hasPermission($allowablePermissions): bool {
		if (is_array($allowablePermissions)) {
			foreach ($allowablePermissions as $perm) {
				if (in_array($perm, $this->permissions)) {
					return true;
				}
			}
			return false;
		}
		return in_array($allowablePermissions, $this->permissions);
	}
}

class OpenAPIAuthorizerTests extends TestCase {
	
	private static $userWithSuperuser = null;
	private static $userWithoutSuperuser = null;
	private static $userWithSpotlightPerm = null;
	
	public static function setUpBeforeClass(): void {
		if (!defined('ROOT_DIR')) {
			define('ROOT_DIR', realpath(__DIR__ . '/../../../../../code/web'));
		}
		
		global $logger;
		if (!isset($logger)) {
			$logger = new MockLogger();
		}
		
		require_once ROOT_DIR . '/sys/API/OpenAPIAuthorizer.php';
		
		self::$userWithSuperuser = new MockUser(['Use All API Endpoints']);
		self::$userWithoutSuperuser = new MockUser([]);
		self::$userWithSpotlightPerm = new MockUser(['Administer All Collection Spotlights']);
	}
	
	protected function setUp(): void {
		\OpenAPIAuthorizer::clearCache();
	}
	
	public function test_publicEndpoint_noAuthRequired(): void {
		$result = \OpenAPIAuthorizer::authorize('TestAPI', 'ping', false, false, false);
		
		$this->assertTrue($result['allowed']);
		$this->assertEquals('public', $result['scope']);
	}
	
	public function test_publicEndpoint_withIpWhitelist(): void {
		$result = \OpenAPIAuthorizer::authorize('TestAPI', 'ping', false, true, false);
		
		$this->assertTrue($result['allowed']);
	}
	
	public function test_userEndpoint_noAuth_denied(): void {
		$result = \OpenAPIAuthorizer::authorize('TestAPI', 'whoami', false, false, false);
		
		$this->assertFalse($result['allowed']);
		$this->assertEquals('unauthorized', $result['error']);
		$this->assertEquals(401, $result['code']);
	}
	
	public function test_userEndpoint_withUser_allowed(): void {
		$result = \OpenAPIAuthorizer::authorize('TestAPI', 'whoami', self::$userWithoutSuperuser, false, false);
		
		$this->assertTrue($result['allowed']);
		$this->assertEquals('user', $result['scope']);
	}
	
	public function test_superuserBypass_accessesGreenhouseOnlyEndpoint(): void {
		$result = \OpenAPIAuthorizer::authorize('TestAPI', 'appInfo', self::$userWithSuperuser, false, false);
		
		$this->assertTrue($result['allowed']);
		$this->assertEquals('superuser', $result['scope']);
	}
	
	public function test_greenhouseEndpoint_withoutGreenhouseAuth_denied(): void {
		$result = \OpenAPIAuthorizer::authorize('TestAPI', 'appInfo', self::$userWithoutSuperuser, false, false);
		
		$this->assertFalse($result['allowed']);
		$this->assertEquals('unauthorized', $result['error']);
	}
	
	public function test_greenhouseEndpoint_withGreenhouseAuth_allowed(): void {
		$result = \OpenAPIAuthorizer::authorize('TestAPI', 'appInfo', false, false, true);
		
		$this->assertTrue($result['allowed']);
		$this->assertEquals('greenhouse', $result['scope']);
	}
	
	public function test_permissionEndpoint_withoutPermission_denied(): void {
		$result = \OpenAPIAuthorizer::authorize('TestAPI', 'adminCheck', self::$userWithoutSuperuser, false, false);
		
		$this->assertFalse($result['allowed']);
		$this->assertEquals('insufficient_permissions', $result['error']);
		$this->assertEquals(403, $result['code']);
	}
	
	public function test_permissionEndpoint_withPermission_allowed(): void {
		$result = \OpenAPIAuthorizer::authorize('TestAPI', 'adminCheck', self::$userWithSpotlightPerm, false, false);
		
		$this->assertTrue($result['allowed']);
		$this->assertEquals('user', $result['scope']);
	}
	
	public function test_ipWhitelist_grantsAccess(): void {
		$result = \OpenAPIAuthorizer::authorize('TestAPI', 'whoami', false, true, false);
		
		$this->assertTrue($result['allowed']);
		$this->assertEquals('ip', $result['scope']);
	}
	
	public function test_ipWhitelist_blockedByIpAllowedFalse(): void {
		$result = \OpenAPIAuthorizer::authorize('TestAPI', 'noIpAccess', false, true, false);
		
		$this->assertFalse($result['allowed']);
		$this->assertEquals('unauthorized', $result['error']);
	}
	
	public function test_ipBlocked_withUserAuth_allowed(): void {
		$result = \OpenAPIAuthorizer::authorize('TestAPI', 'noIpAccess', self::$userWithoutSuperuser, true, false);
		
		$this->assertTrue($result['allowed']);
		$this->assertEquals('user', $result['scope']);
	}
	
	public function test_flexibleAuth_withUser_allowed(): void {
		$result = \OpenAPIAuthorizer::authorize('TestAPI', 'flexibleAuth', self::$userWithoutSuperuser, false, false);
		
		$this->assertTrue($result['allowed']);
		$this->assertEquals('user', $result['scope']);
	}
	
	public function test_flexibleAuth_withGreenhouse_allowed(): void {
		$result = \OpenAPIAuthorizer::authorize('TestAPI', 'flexibleAuth', false, false, true);
		
		$this->assertTrue($result['allowed']);
		$this->assertEquals('greenhouse', $result['scope']);
	}
	
	public function test_undefinedMethod_returns404(): void {
		$result = \OpenAPIAuthorizer::authorize('TestAPI', 'nonExistentMethod', false, false, false);
		
		$this->assertFalse($result['allowed']);
		$this->assertEquals('invalid_method', $result['error']);
		$this->assertEquals(404, $result['code']);
	}
	
	public function test_methodExists_returnsTrue(): void {
		$this->assertTrue(\OpenAPIAuthorizer::methodExists('TestAPI', 'ping'));
		$this->assertTrue(\OpenAPIAuthorizer::methodExists('TestAPI', 'whoami'));
	}
	
	public function test_methodExists_returnsFalse(): void {
		$this->assertFalse(\OpenAPIAuthorizer::methodExists('TestAPI', 'fakeMethod'));
		$this->assertFalse(\OpenAPIAuthorizer::methodExists('FakeAPI', 'ping'));
	}
}
