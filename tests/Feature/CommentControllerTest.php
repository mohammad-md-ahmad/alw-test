<?php

namespace Tests\Feature;

use App\Contracts\CommentServiceInterface;
use App\Models\Comment;
use App\Models\User;
use App\Services\CommentService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class CommentControllerTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;
    use WithoutMiddleware;

    private $fixture;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpFaker();
        $this->fixture = app()->make(CommentServiceInterface::class);

        $this->actingAs(User::factory()->create(),'web');
    }

    public function testInstance()
    {
        $this->assertInstanceOf(CommentServiceInterface::class, $this->fixture);
        $this->assertInstanceOf(CommentService::class, $this->fixture);   // tests service provider binding
    }

    public function testQuery()
    {
        $comment1 = Comment::factory()->create();

        $url = sprintf('api/comment/%s', $comment1->id);

        $response = $this->get($url);

        $response->assertOk();
        $response->assertSimilarJson([
            'id' => $comment1->id,
            'name' => $comment1->commenter_name,
            'message' => $comment1->message,
            'comments' => []
        ]);
    }

    public function testStore()
    {
        $comment = Comment::factory()->make()->toArray();

        $response = $this->post('api/comment', $comment);

        $this->assertEquals($response[0]['name'], $comment['commenter_name']);
    }

    public function testUpdate()
    {
        $comment = Comment::factory()->create()->toArray();

        $updatedComment = Comment::factory([
            'id' => $comment['id'],
            'commenter_name' => 'Commenter name edited',
        ])->make()->toArray();

        $url = sprintf('api/comment/%s', $comment['id']);

        $response = $this->put($url, $updatedComment);

        $response->assertStatus(200);
        $this->assertDatabaseHas('comments', $updatedComment);
        $this->assertDatabaseMissing('comments', $comment);
    }

    public function testDelete()
    {
        $comment = Comment::factory()->create()->toArray();

        $url = sprintf('api/comment/%s', $comment['id']);
        $response = $this->delete($url);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('comments', $comment);
    }
}
