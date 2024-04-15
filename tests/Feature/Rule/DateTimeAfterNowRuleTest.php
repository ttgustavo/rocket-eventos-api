<?php

namespace Tests\Feature\Rule;

use App\Presenter\Rules\DateTimeAfterNowRule;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class DateTimeAfterNowRuleTest extends TestCase
{
    /** @var ValidationRule */
    private $rule;
    /**
     * @var \stdClass|MockObject
     * @method \Closure abc abc() My method
     */
    private $anonymousClass;

    protected function setUp(): void
    {
        $this->rule = new DateTimeAfterNowRule();
        $this->anonymousClass = $this->getMockBuilder(\stdClass::class)->getMock();
    }

    public function testPassWhenDateIsAfter(): void
    {
        $now = Carbon::now();
        $before = $now->addHours(3);
        $callbackCalled = false;
        $callback = function (string $message) use (&$callbackCalled) {
            $callbackCalled = true;
        };

        $this->rule->validate(':datetime', $before->toISOString(), $callback);
        $this->assertSame(false, $callbackCalled);
    }

    public function testFailWhenDateIsBefore(): void
    {
        $callbackCalled = false;
        $now = Carbon::now();
        $before = $now->subHours(3);
        $callback = function (string $_) use (&$callbackCalled) {
            $callbackCalled = true;
        };

        $this->rule->validate(':datetime', $before->toISOString(), $callback);

        $this->assertSame(true, $callbackCalled);
    }

    public function testFailWhenDateIsSame(): void
    {
        $callbackCalled = false;
        $now = Carbon::now();
        $before = $now->copy();
        $callback = function (string $_) use (&$callbackCalled) {
            $callbackCalled = true;
        };

        $this->rule->validate(':datetime', $before->toISOString(), $callback);

        $this->assertSame(true, $callbackCalled);
    }
}