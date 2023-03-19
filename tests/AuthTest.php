<?php declare(strict_types=1);
    use PHPUnit\Framework\TestCase;

    use Tjall\Magister\Sessions\LoginSession;
    use Tjall\Magister\Tests\Mocks\AuthMock;

    use function PHPUnit\Framework\assertArrayHasKey;
    use function PHPUnit\Framework\assertStringStartsWith;

    final class AuthTest extends TestCase {
        public function testCanLogin() {
            $credentials = AuthMock::getLoginCredentials();
            
            $session = new LoginSession();
            $session->performChallenge('tenant', $credentials['tenant']);
            $session->performChallenge('username', $credentials['username']);
            $session->performChallenge('password', $credentials['password']);
            
            $bearer = $session->submit();
            
            assertArrayHasKey('accountId', $bearer->data, 'Failed to get accountId.');
            assertStringStartsWith('http', @$bearer->data['apiUri'], 'Failed to get apiUri.');
        
            AuthMock::setBearer($bearer);
        }
    }