<?php

namespace App\Tests;

use App\Services\DueDateCalculator;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(DueDateCalculator::class)]
class CalculatorTest extends KernelTestCase
{
    /**
     * @return void
     * @throws Exception
     */
    public function testBasicValidData(): void
    {
        $calculator = new DueDateCalculator();

        $submit_date = '2024-05-15 12:00';
        $turnaround_time = 40;

        $due_date = $calculator->calculateDueDate($submit_date, $turnaround_time);
        $this->assertEquals('2024-05-22 12:00', $due_date->format('Y-m-d H:i'));

        $submit_date = '2024-05-28 2:12PM';
        $turnaround_time = 16;

        $due_date = $calculator->calculateDueDate($submit_date, $turnaround_time);
        $this->assertEquals('2024-05-30 14:12', $due_date->format('Y-m-d H:i'));
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testDifferentTurnaroundTimes(): void
    {
        $calculator = new DueDateCalculator();

        $submit_date = '2024-05-15 12:00';
        $turnaround_time = 2;

        $due_date = $calculator->calculateDueDate($submit_date, $turnaround_time);
        $this->assertEquals('2024-05-15 14:00', $due_date->format('Y-m-d H:i'));

        $turnaround_time = 6;

        $due_date = $calculator->calculateDueDate($submit_date, $turnaround_time);
        $this->assertEquals('2024-05-16 10:00', $due_date->format('Y-m-d H:i'));

        $turnaround_time = 12;

        $due_date = $calculator->calculateDueDate($submit_date, $turnaround_time);
        $this->assertEquals('2024-05-16 16:00', $due_date->format('Y-m-d H:i'));

        $turnaround_time = 13;

        $due_date = $calculator->calculateDueDate($submit_date, $turnaround_time);
        $this->assertEquals('2024-05-16 17:00', $due_date->format('Y-m-d H:i'));
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testMinutes(): void
    {
        $calculator = new DueDateCalculator();

        $submit_date = '2024-05-15 15:59';
        $turnaround_time = 1;

        $due_date = $calculator->calculateDueDate($submit_date, $turnaround_time);
        $this->assertEquals('2024-05-15 16:59', $due_date->format('Y-m-d H:i'));

        $turnaround_time = 2;

        $due_date = $calculator->calculateDueDate($submit_date, $turnaround_time);
        $this->assertEquals('2024-05-16 09:59', $due_date->format('Y-m-d H:i'));
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testBeforeOutOfHours(): void
    {
        $turnaround_time = 1;
        $calculator = new DueDateCalculator();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid submit date provided!');

        $submit_date = '2024-05-15 08:59';
        $calculator->calculateDueDate($submit_date, $turnaround_time);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testAfterOutOfHours(): void
    {
        $turnaround_time = 1;
        $calculator = new DueDateCalculator();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid submit date provided!');

        $submit_date = '2024-05-15 17:01';
        $calculator->calculateDueDate($submit_date, $turnaround_time);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testNegativeTurnaround(): void
    {
        $turnaround_time = -1;
        $calculator = new DueDateCalculator();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid turnaround time provided!');

        $submit_date = '2024-05-15 15:30';
        $calculator->calculateDueDate($submit_date, $turnaround_time);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testInvalidDate(): void
    {
        $turnaround_time = 8;
        $calculator = new DueDateCalculator();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid submit date provided!');

        $submit_date = '2024-13-35 17:01';
        $calculator->calculateDueDate($submit_date, $turnaround_time);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testInvalidTime(): void
    {
        $turnaround_time = 8;
        $calculator = new DueDateCalculator();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid submit date provided!');

        $submit_date = '2024-13-35 17:90';
        $calculator->calculateDueDate($submit_date, $turnaround_time);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testAmPm(): void
    {
        $calculator = new DueDateCalculator();

        $submit_date = '2024-05-15 9:30AM';
        $turnaround_time = 3;

        $due_date = $calculator->calculateDueDate($submit_date, $turnaround_time);
        $this->assertEquals('2024-05-15 12:30', $due_date->format('Y-m-d H:i'));

        $submit_date = '2024-05-15 2:30PM';
        $turnaround_time = 6;

        $due_date = $calculator->calculateDueDate($submit_date, $turnaround_time);
        $this->assertEquals('2024-05-16 12:30', $due_date->format('Y-m-d H:i'));
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testWeekends(): void
    {
        $calculator = new DueDateCalculator();

        $submit_date = '2024-05-24 12:00';
        $turnaround_time = 10;

        $due_date = $calculator->calculateDueDate($submit_date, $turnaround_time);
        $this->assertEquals('2024-05-27 14:00', $due_date->format('Y-m-d H:i'));

        $submit_date = '2024-05-25 12:00';
        $turnaround_time = 11;

        $due_date = $calculator->calculateDueDate($submit_date, $turnaround_time);
        $this->assertEquals('2024-05-28 12:00', $due_date->format('Y-m-d H:i'));

        $submit_date = '2024-05-26 16:00';
        $turnaround_time = 5;

        $due_date = $calculator->calculateDueDate($submit_date, $turnaround_time);
        $this->assertEquals('2024-05-27 14:00', $due_date->format('Y-m-d H:i'));
    }
}