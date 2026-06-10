<?php

namespace Tests\Unit\Services;

use App\Exceptions\InvalidTransitionException;
use App\Models\Akreditasi;
use App\Models\AkreditasiAuditLog;
use App\Models\User;
use App\Services\AkreditasiStateMachine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AkreditasiStateMachineTest extends TestCase
{
    use RefreshDatabase;

    private AkreditasiStateMachine $stateMachine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stateMachine = new AkreditasiStateMachine();
    }

    private function createActor(): User
    {
        return User::factory()->create(['role_id' => 1]);
    }

    private function createAkreditasi(string $status): Akreditasi
    {
        $owner = User::factory()->create(['role_id' => 3]);

        return Akreditasi::create([
            'user_id' => $owner->id,
            'uuid' => (string) Str::uuid(),
            'status' => $status,
        ]);
    }

    public function test_valid_transition_draft_to_initial_submitted(): void
    {
        $akreditasi = $this->createAkreditasi(AkreditasiStateMachine::STATUS_DRAFT_PROFILE);
        $actor = $this->createActor();

        $result = $this->stateMachine->transition(
            $akreditasi,
            AkreditasiStateMachine::STATUS_INITIAL_SUBMITTED,
            $actor
        );

        $this->assertEquals(AkreditasiStateMachine::STATUS_INITIAL_SUBMITTED, $result->status);
        $this->assertEquals($actor->id, $result->status_changed_by);

        $this->assertDatabaseHas('akreditasi_audit_logs', [
            'akreditasi_id' => $akreditasi->id,
            'from_status' => AkreditasiStateMachine::STATUS_DRAFT_PROFILE,
            'to_status' => AkreditasiStateMachine::STATUS_INITIAL_SUBMITTED,
            'actor_user_id' => $actor->id,
        ]);
    }

    public function test_valid_transition_initial_submitted_to_assessment_open(): void
    {
        $akreditasi = $this->createAkreditasi(AkreditasiStateMachine::STATUS_INITIAL_SUBMITTED);
        $actor = $this->createActor();

        $result = $this->stateMachine->transition(
            $akreditasi,
            AkreditasiStateMachine::STATUS_ASSESSMENT_OPEN,
            $actor
        );

        $this->assertEquals(AkreditasiStateMachine::STATUS_ASSESSMENT_OPEN, $result->status);
    }

    public function test_valid_transition_assessment_open_to_admin_stage_1_review(): void
    {
        $akreditasi = $this->createAkreditasi(AkreditasiStateMachine::STATUS_ASSESSMENT_OPEN);
        $actor = $this->createActor();

        $result = $this->stateMachine->transition(
            $akreditasi,
            AkreditasiStateMachine::STATUS_ADMIN_STAGE_1_REVIEW,
            $actor
        );

        $this->assertEquals(AkreditasiStateMachine::STATUS_ADMIN_STAGE_1_REVIEW, $result->status);
    }

    public function test_valid_transition_admin_stage_1_review_to_correction(): void
    {
        $akreditasi = $this->createAkreditasi(AkreditasiStateMachine::STATUS_ADMIN_STAGE_1_REVIEW);
        $actor = $this->createActor();

        $result = $this->stateMachine->transition(
            $akreditasi,
            AkreditasiStateMachine::STATUS_ADMIN_STAGE_1_CORRECTION,
            $actor
        );

        $this->assertEquals(AkreditasiStateMachine::STATUS_ADMIN_STAGE_1_CORRECTION, $result->status);
    }

    public function test_valid_transition_correction_to_review(): void
    {
        $akreditasi = $this->createAkreditasi(AkreditasiStateMachine::STATUS_ADMIN_STAGE_1_CORRECTION);
        $actor = $this->createActor();

        $result = $this->stateMachine->transition(
            $akreditasi,
            AkreditasiStateMachine::STATUS_ADMIN_STAGE_1_REVIEW,
            $actor
        );

        $this->assertEquals(AkreditasiStateMachine::STATUS_ADMIN_STAGE_1_REVIEW, $result->status);
    }

    public function test_valid_transition_final_approved_to_completed(): void
    {
        $akreditasi = $this->createAkreditasi(AkreditasiStateMachine::STATUS_FINAL_APPROVED);
        $actor = $this->createActor();

        $result = $this->stateMachine->transition(
            $akreditasi,
            AkreditasiStateMachine::STATUS_COMPLETED,
            $actor
        );

        $this->assertEquals(AkreditasiStateMachine::STATUS_COMPLETED, $result->status);
    }

    public function test_invalid_transition_throws_exception(): void
    {
        $akreditasi = $this->createAkreditasi(AkreditasiStateMachine::STATUS_DRAFT_PROFILE);
        $actor = $this->createActor();

        $this->expectException(InvalidTransitionException::class);

        $this->stateMachine->transition(
            $akreditasi,
            AkreditasiStateMachine::STATUS_COMPLETED,
            $actor
        );
    }

    public function test_invalid_transition_skip_status(): void
    {
        $akreditasi = $this->createAkreditasi(AkreditasiStateMachine::STATUS_DRAFT_PROFILE);
        $actor = $this->createActor();

        $this->expectException(InvalidTransitionException::class);

        $this->stateMachine->transition(
            $akreditasi,
            AkreditasiStateMachine::STATUS_ASSESSMENT_OPEN,
            $actor
        );
    }

    public function test_terminal_status_has_no_transitions(): void
    {
        $akreditasi = $this->createAkreditasi(AkreditasiStateMachine::STATUS_COMPLETED);
        $actor = $this->createActor();

        $this->expectException(InvalidTransitionException::class);

        $this->stateMachine->transition(
            $akreditasi,
            AkreditasiStateMachine::STATUS_INITIAL_SUBMITTED,
            $actor
        );
    }

    public function test_can_transition_returns_true_for_valid(): void
    {
        $this->assertTrue($this->stateMachine->canTransition(
            AkreditasiStateMachine::STATUS_DRAFT_PROFILE,
            AkreditasiStateMachine::STATUS_INITIAL_SUBMITTED
        ));
    }

    public function test_can_transition_returns_false_for_invalid(): void
    {
        $this->assertFalse($this->stateMachine->canTransition(
            AkreditasiStateMachine::STATUS_DRAFT_PROFILE,
            AkreditasiStateMachine::STATUS_COMPLETED
        ));
    }

    public function test_is_terminal_returns_true_for_completed(): void
    {
        $this->assertTrue($this->stateMachine->isTerminal(AkreditasiStateMachine::STATUS_COMPLETED));
    }

    public function test_is_terminal_returns_false_for_active(): void
    {
        $this->assertFalse($this->stateMachine->isTerminal(AkreditasiStateMachine::STATUS_ADMIN_STAGE_1_REVIEW));
    }

    public function test_is_correction_stage_returns_true(): void
    {
        $this->assertTrue($this->stateMachine->isCorrectionStage(AkreditasiStateMachine::STATUS_ADMIN_STAGE_1_CORRECTION));
        $this->assertFalse($this->stateMachine->isCorrectionStage(AkreditasiStateMachine::STATUS_ADMIN_STAGE_1_REVIEW));
    }

    public function test_audit_log_created_on_transition(): void
    {
        $akreditasi = $this->createAkreditasi(AkreditasiStateMachine::STATUS_DRAFT_PROFILE);
        $actor = $this->createActor();

        $this->stateMachine->transition(
            $akreditasi,
            AkreditasiStateMachine::STATUS_INITIAL_SUBMITTED,
            $actor,
            'Submitted for review'
        );

        $log = AkreditasiAuditLog::where('akreditasi_id', $akreditasi->id)->first();

        $this->assertNotNull($log);
        $this->assertEquals(AkreditasiStateMachine::STATUS_DRAFT_PROFILE, $log->from_status);
        $this->assertEquals(AkreditasiStateMachine::STATUS_INITIAL_SUBMITTED, $log->to_status);
        $this->assertEquals($actor->id, $log->actor_user_id);
        $this->assertEquals('Submitted for review', $log->reason);
    }
}
