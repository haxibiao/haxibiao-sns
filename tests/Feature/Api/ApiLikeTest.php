<?php

namespace Haxibiao\Sns\Tests\Feature\Api;

use Tests\TestCase;

class ApiLikeTest extends TestCase
{

    public function testLikeApi()
    {
        $user    = \App\User::orderBy('id', 'desc')->take(5)->get()->random();
        $article = \App\Article::orderBy('id', 'desc')->take(5)->get()->random();

        $response = $this->post("/api/like/{$article->id}/article", [
            'api_token' => $user->api_token,
        ]);

        $response->assertStatus(200);
        $response->assertSeeText('liked'); //TODO: should assertJson
    }

}
