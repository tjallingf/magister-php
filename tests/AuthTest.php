<?php declare(strict_types=1);
    use PHPUnit\Framework\TestCase;

    use Tjall\Magister\Tests\Mocks\AuthMock;

    use function PHPUnit\Framework\assertArrayHasKey;
    use function PHPUnit\Framework\assertIsNumeric;

    final class AuthTest extends TestCase {
        public function testCanLogin() {
            $session = AuthMock::getSession();

            assertArrayHasKey('accessToken', $session->bearer->toArray(), 'Failed to get accessToken.');
            assertArrayHasKey('accountId', $session->bearer->toArray(), 'Failed to get accountId.');
        }
    }