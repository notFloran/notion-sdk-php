<?php

namespace Notion\Test\Blocks;

use Notion\Blocks\Paragraph;
use Notion\Common\RichText;
use PHPUnit\Framework\TestCase;

class ParagraphTest extends TestCase
{
    public function test_create_empty_paragraph(): void
    {
        $paragraph = Paragraph::create();

        $this->assertEmpty($paragraph->text());
        $this->assertEmpty($paragraph->children());
    }

    public function test_create_from_string(): void
    {
        $paragraph = Paragraph::fromString("Dummy paragraph.");

        $this->assertEquals("Dummy paragraph.", $paragraph->toString());
    }

    public function test_create_from_array(): void
    {
        $array = [
            "object"           => "block",
            "id"               => "04a13895-f072-4814-8af7-cd11af127040",
            "created_time"     => "2021-10-18T17:09:00.000Z",
            "last_edited_time" => "2021-10-18T17:09:00.000Z",
            "archived"         => false,
            "has_children"     => false,
            "type"             => "paragraph",
            "paragraph"        => [
                "text" => [
                    [
                        "plain_text"  => "Notion paragraphs ",
                        "href"        => null,
                        "type"        => "text",
                        "text"        => [
                            "content" => "Notion paragraphs ",
                            "link" => null,
                        ],
                        "annotations" => [
                            "bold"          => false,
                            "italic"        => false,
                            "strikethrough" => false,
                            "underline"     => false,
                            "code"          => false,
                            "color"         => "default",
                        ],
                    ],
                    [
                        "plain_text"  => "rock!",
                        "href"        => null,
                        "type"        => "text",
                        "text"        => [
                            "content" => "rock!",
                            "link" => null,
                        ],
                        "annotations" => [
                            "bold"          => true,
                            "italic"        => false,
                            "strikethrough" => false,
                            "underline"     => false,
                            "code"          => false,
                            "color"         => "red",
                        ],
                    ],
                ],
                "children" => [],
            ],
        ];

        $paragraph = Paragraph::fromArray($array);

        $this->assertCount(2, $paragraph->text());
        $this->assertEmpty($paragraph->children());
        $this->assertEquals("Notion paragraphs rock!", $paragraph->toString());
        $this->assertFalse($paragraph->block()->archived());
    }

    public function test_error_on_wrong_type(): void
    {
        $this->expectException(\Exception::class);
        $array = [
            "object"           => "block",
            "id"               => "04a13895-f072-4814-8af7-cd11af127040",
            "created_time"     => "2021-10-18T17:09:00.000Z",
            "last_edited_time" => "2021-10-18T17:09:00.000Z",
            "archived"         => false,
            "has_children"     => false,
            "type"             => "wrong-type",
            "paragraph"        => [
                "text"     => [],
                "children" => [],
            ],
        ];

        $paragraph = Paragraph::fromArray($array);
    }

    public function test_transform_in_array(): void
    {
        $p = Paragraph::fromString("Simple paragraph");

        $expected = [
            "object"           => "block",
            "created_time"     => $p->block()->createdTime()->format(DATE_ISO8601),
            "last_edited_time" => $p->block()->lastEditedType()->format(DATE_ISO8601),
            "archived"         => false,
            "has_children"      => false,
            "type"             => "paragraph",
            "paragraph"        => [
                "text" => [[
                    "plain_text"  => "Simple paragraph",
                    "href"        => null,
                    "type"        => "text",
                    "text"        => [
                        "content" => "Simple paragraph",
                        "link" => null,
                    ],
                    "annotations" => [
                        "bold"          => false,
                        "italic"        => false,
                        "strikethrough" => false,
                        "underline"     => false,
                        "code"          => false,
                        "color"         => "default",
                    ],
                ]],
                "children" => [],
            ],
        ];

        $this->assertEquals($expected, $p->toArray());
    }

    public function test_replace_text(): void
    {
        $oldParagraph = Paragraph::fromString("This is an old paragraph");

        $newParagraph = $oldParagraph->withText(
            RichText::createText("This is a "),
            RichText::createText("new paragraph"),
        );

        $this->assertEquals("This is an old paragraph", $oldParagraph->toString());
        $this->assertEquals("This is a new paragraph", $newParagraph->toString());
    }

    public function test_append_text(): void
    {
        $oldParagraph = Paragraph::fromString("A paragraph");

        $newParagraph = $oldParagraph->appendText(
            RichText::createText(" can be extended.")
        );

        $this->assertEquals("A paragraph", $oldParagraph->toString());
        $this->assertEquals("A paragraph can be extended.", $newParagraph->toString());
    }

    public function test_replace_children(): void
    {
        $paragraph = Paragraph::fromString("Simple paragraph.")->withChildren(
            Paragraph::fromString("Nested paragraph 1"),
            Paragraph::fromString("Nested paragraph 2"),
        );

        $this->assertCount(2, $paragraph->children());
        $this->assertEquals("Nested paragraph 1", $paragraph->children()[0]->toString());
        $this->assertEquals("Nested paragraph 2", $paragraph->children()[1]->toString());
    }

    public function test_append_child(): void
    {
        $paragraph = Paragraph::fromString("Simple paragraph.");
        $paragraph = $paragraph->appendChild(Paragraph::fromString("Nested paragraph"));

        $this->assertCount(1, $paragraph->children());
        $this->assertEquals("Nested paragraph", $paragraph->children()[0]->toString());
    }
}