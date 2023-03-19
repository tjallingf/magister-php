<?php declare(strict_types=1);
    use PHPUnit\Framework\TestCase;

    use Tjall\Magister\Controllers\AppointmentController;
    use Tjall\Magister\Tests\Mocks\AuthMock;

    final class AppointmentsTest extends TestCase {
        public static AppointmentController $controller;
        public static AppointmentController $filteredController;

        public static function setUpBeforeClass(): void {
            static::$controller = new AppointmentController(AuthMock::getSession());
            static::$filteredController = new AppointmentController(AuthMock::getSession(), [
                'filter' => [ 'teachers', 'description', 'location' ]
            ]);
        }

        public function testCanIndex() {
            $appointments = static::$controller->index();
            $this->assertIsArray($appointments, 'Failed to get appointments.');
        }

        public function testCanIndexWithDateRange() {
            $appointments = static::$controller->index(strtotime('2023-03-19'), strtotime('2023-03-26'));
            $this->assertIsArray($appointments, 'Failed to get appointments.');
        }

        public function testCanIndexWithFilter() {
            $appointments = static::$filteredController->index(strtotime('2023-03-19'), strtotime('2023-03-26'));
            $this->assertIsArray($appointments, 'Failed to get appointments.');
            $this->assertIsArray($appointments[0], 'No appointments were found.');
            $this->assertArrayNotHasKey('id', $appointments[0], 'Failed to filter appointment properties.');

        }
    }