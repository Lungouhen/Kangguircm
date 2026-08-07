<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\PostStatus;
use App\Enums\SubscriberStatus;
use App\Enums\EmployeeStatus;
use App\Enums\LeaveType;
use App\Enums\LeaveStatus;
use App\Enums\CampaignStatus;
use PHPUnit\Framework\TestCase;

/**
 * Tests that all enums follow the php-pro backed enum pattern.
 */
class EnumPatternTest extends TestCase
{
    public function test_all_enums_are_backed_strings(): void
    {
        $enums = [
            PostStatus::class,
            SubscriberStatus::class,
            EmployeeStatus::class,
            LeaveType::class,
            LeaveStatus::class,
            CampaignStatus::class,
        ];

        foreach ($enums as $enum) {
            $reflection = new \ReflectionEnum($enum);
            $this->assertTrue($reflection->isBacked(), "$enum must be a backed enum");
            $this->assertSame('string', $reflection->getBackingType()->getName(), "$enum must back to string");
        }
    }

    public function test_post_status_cases(): void
    {
        $cases = PostStatus::cases();
        $this->assertCount(3, $cases);
        $values = array_map(fn(PostStatus $c) => $c->value, $cases);
        $this->assertContains('draft', $values);
        $this->assertContains('published', $values);
        $this->assertContains('archived', $values);
    }

    public function test_leave_type_cases(): void
    {
        $cases = LeaveType::cases();
        $this->assertCount(5, $cases);
    }

    public function test_enum_from_round_trip(): void
    {
        $this->assertSame(PostStatus::Draft, PostStatus::from('draft'));
        $this->assertSame('draft', PostStatus::Draft->value);
    }

    public function test_enum_try_from_returns_null_for_invalid(): void
    {
        $this->assertNull(PostStatus::tryFrom('nonexistent'));
        $this->assertNull(LeaveStatus::tryFrom('invalid'));
    }
}
