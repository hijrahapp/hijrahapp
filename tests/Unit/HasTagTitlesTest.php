<?php

namespace Tests\Unit;

use App\Models\Tag;
use App\Traits\HasTagTitles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HasTagTitlesTest extends TestCase
{
    use RefreshDatabase, HasTagTitles;

    public function test_get_tag_titles_converts_ids_to_titles()
    {
        // Create test tags
        $tag1 = Tag::create(['title' => 'Tag One', 'active' => true]);
        $tag2 = Tag::create(['title' => 'Tag Two', 'active' => true]);
        $tag3 = Tag::create(['title' => 'Tag Three', 'active' => true]);

        // Test with valid tag IDs
        $tagIds = [$tag1->id, $tag2->id, $tag3->id];
        $expectedTitles = ['Tag One', 'Tag Two', 'Tag Three'];

        $result = $this->getTagTitles($tagIds);
        $this->assertEquals($expectedTitles, $result);
    }

    public function test_get_tag_titles_returns_empty_array_for_empty_input()
    {
        $result = $this->getTagTitles([]);
        $this->assertEquals([], $result);
    }

    public function test_get_tag_titles_returns_empty_array_for_null_input()
    {
        $result = $this->getTagTitles(null);
        $this->assertEquals([], $result);
    }

    public function test_get_tag_titles_handles_invalid_tag_ids()
    {
        // Create one valid tag
        $tag = Tag::create(['title' => 'Valid Tag', 'active' => true]);

        // Test with mix of valid and invalid IDs
        $tagIds = [$tag->id, 999, 1000]; // 999 and 1000 don't exist
        $expectedTitles = ['Valid Tag']; // Only the valid tag should be returned

        $result = $this->getTagTitles($tagIds);
        $this->assertEquals($expectedTitles, $result);
    }
} 