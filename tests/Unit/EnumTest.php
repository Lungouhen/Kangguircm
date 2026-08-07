<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\PostStatus;
use App\Enums\SubscriberStatus;
use App\Enums\EmployeeStatus;
use App\Enums\LeaveType;
use App\Enums\LeaveStatus;
use App\Enums\CampaignStatus;
use App\Enums\AttendanceStatus;
use App\Enums\PayrollStatus;
use App\Enums\PageStatus;
use PHPUnit\Framework\TestCase;

/**
 * Tests for all backed enum types.
 *
 * Verifies enum values, labels, and business logic methods.
 */
class EnumTest extends TestCase
{
    public function test_post_status_values(): void
    {
        $this->assertSame('draft', PostStatus::Draft->value);
        $this->assertSame('published', PostStatus::Published->value);
        $this->assertSame('archived', PostStatus::Archived->value);
    }

    public function test_post_status_labels(): void
    {
        $this->assertSame('Draft', PostStatus::Draft->label());
        $this->assertSame('Published', PostStatus::Published->label());
        $this->assertSame('Archived', PostStatus::Archived->label());
    }

    public function test_post_status_is_public(): void
    {
        $this->assertFalse(PostStatus::Draft->isPublic());
        $this->assertTrue(PostStatus::Published->isPublic());
        $this->assertFalse(PostStatus::Archived->isPublic());
    }

    public function test_post_status_from_value(): void
    {
        $this->assertSame(PostStatus::Draft, PostStatus::from('draft'));
        $this->assertSame(PostStatus::Published, PostStatus::from('published'));
    }

    public function test_subscriber_status_can_receive_email(): void
    {
        $this->assertTrue(SubscriberStatus::Active->canReceiveEmail());
        $this->assertFalse(SubscriberStatus::Unsubscribed->canReceiveEmail());
        $this->assertFalse(SubscriberStatus::Bounced->canReceiveEmail());
    }

    public function test_employee_status_can_clock_in(): void
    {
        $this->assertTrue(EmployeeStatus::Active->canClockIn());
        $this->assertFalse(EmployeeStatus::Inactive->canClockIn());
        $this->assertFalse(EmployeeStatus::Terminated->canClockIn());
    }

    public function test_leave_status_is_resolved(): void
    {
        $this->assertFalse(LeaveStatus::Pending->isResolved());
        $this->assertTrue(LeaveStatus::Approved->isResolved());
        $this->assertTrue(LeaveStatus::Rejected->isResolved());
    }

    public function test_campaign_status_is_terminal(): void
    {
        $this->assertFalse(CampaignStatus::Draft->isTerminal());
        $this->assertFalse(CampaignStatus::Scheduled->isTerminal());
        $this->assertFalse(CampaignStatus::Sending->isTerminal());
        $this->assertTrue(CampaignStatus::Sent->isTerminal());
    }

    public function test_leave_type_labels(): void
    {
        $this->assertSame('Sick Leave', LeaveType::Sick->label());
        $this->assertSame('Casual Leave', LeaveType::Casual->label());
        $this->assertSame('Earned Leave', LeaveType::Earned->label());
        $this->assertSame('Maternity Leave', LeaveType::Maternity->label());
        $this->assertSame('Paternity Leave', LeaveType::Paternity->label());
    }

    public function test_attendance_status_labels(): void
    {
        $this->assertSame('Present', AttendanceStatus::Present->label());
        $this->assertSame('Absent', AttendanceStatus::Absent->label());
        $this->assertSame('Late', AttendanceStatus::Late->label());
        $this->assertSame('Half Day', AttendanceStatus::HalfDay->label());
    }

    public function test_payroll_status_values(): void
    {
        $this->assertSame('pending', PayrollStatus::Pending->value);
        $this->assertSame('processed', PayrollStatus::Processed->value);
        $this->assertSame('paid', PayrollStatus::Paid->value);
    }

    public function test_page_status_values(): void
    {
        $this->assertSame('draft', PageStatus::Draft->value);
        $this->assertSame('published', PageStatus::Published->value);
    }
}
