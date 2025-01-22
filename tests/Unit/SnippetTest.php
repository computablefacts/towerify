<?php

namespace Tests\Unit;

use App\Helpers\Snippet;
use Tests\TestCaseNoDb;

class SnippetTest extends TestCaseNoDb
{
    public function testSearchedWordsNotInText()
    {
        $snippets = Snippet::extract(["not_here"], "Hello world!");
        $this->assertTrue($snippets->isEmpty());
    }

    public function testTextShorterThanSnippetMaxLength()
    {
        $snippets = Snippet::extract(["world"], "Hello world!");
        $this->assertEquals("Hello world!", $snippets->first());
    }

    public function testSnippetWithLeftEllipsis()
    {
        $snippets = Snippet::extract(["Yahoo", "Outlook"], $this->text());
        $this->assertEquals("...in-the-know with the latest news and information. CloudSponge provides an interface to easily enable your users to import contacts from a variety of the most popular webmail services including Yahoo, Gmail and Hotmail/MSN as well as popular desktop address books such as Mac Address Book and Outlook.", $snippets->first());
    }

    public function testSnippetWithoutLeftEllipsis()
    {
        $snippets = Snippet::extractEx(["Yahoo", "Outlook"], $this->text(), 300, 50, "");
        $this->assertEquals("in-the-know with the latest news and information. CloudSponge provides an interface to easily enable your users to import contacts from a variety of the most popular webmail services including Yahoo, Gmail and Hotmail/MSN as well as popular desktop address books such as Mac Address Book and Outlook.", $snippets->first());
    }

    public function testSnippetWithRightEllipsis()
    {
        $snippets = Snippet::extract(["most", "visited", "home", "page"], $this->text());
        $this->assertEquals("Welcome to Yahoo!, the world’s most visited home page. Quickly find what you’re searching for, get in touch with friends and stay in-the-know with the latest news and information. CloudSponge provides an interface to easily enable your users to import contacts from a variety of the most popular webmail...", $snippets->first());
    }

    public function testSnippetWithLeftAndRightEllipsis()
    {
        $snippets = Snippet::extract(["latest", "news", "CloudSponge"], $this->text());
        $this->assertEquals("...in touch with friends and stay in-the-know with the latest news and information. CloudSponge provides an interface to easily enable your users to import contacts from a variety of the most popular webmail services including Yahoo, Gmail and Hotmail/MSN as well as popular desktop address books such as Mac...", $snippets->first());
    }

    public function testSnippetWithEmptyIndicator()
    {
        $snippets = Snippet::extractEx(["latest", "news", "CloudSponge"], $this->text(), 300, 50, "");
        $this->assertEquals("in touch with friends and stay in-the-know with the latest news and information. CloudSponge provides an interface to easily enable your users to import contacts from a variety of the most popular webmail services including Yahoo, Gmail and Hotmail/MSN as well as popular desktop address books such as Mac", $snippets->first());
    }

    public function testSnippetWithNullIndicator()
    {
        $snippets = Snippet::extractEx(["latest", "news", "CloudSponge"], $this->text(), 300, 50, null);
        $this->assertEquals("...in touch with friends and stay in-the-know with the latest news and information. CloudSponge provides an interface to easily enable your users to import contacts from a variety of the most popular webmail services including Yahoo, Gmail and Hotmail/MSN as well as popular desktop address books such as Mac...", $snippets->first());
    }

    public function testDoNotTruncateHead()
    {
        $snippets = Snippet::extractEx(["gmail"], "zor@gmail.com", 5, 0, "...");
        $this->assertEquals("...gmail...", $snippets->first());
    }

    public function testDoNotTruncateHeadWithNullIndicator()
    {
        $snippets = Snippet::extractEx(["gmail"], "zor@gmail.com", 5, 0, null);
        $this->assertEquals("...gmail...", $snippets->first());
    }

    public function testDoNotTruncateHeadWithEmptyIndicator()
    {
        $snippets = Snippet::extractEx(["gmail"], "zor@gmail.com", 5, 0, "");
        $this->assertEquals("gmail", $snippets->first());
    }

    public function testDoNotTruncateTail()
    {
        $snippets = Snippet::extractEx(["zor"], "zor@gmail.com", 3, 50, "...");
        $this->assertEquals("zor@gmail.com", $snippets->first());
    }

    public function testDoNotTruncateTailWithNullIndicator()
    {
        $snippets = Snippet::extractEx(["zor"], "zor@gmail.com", 3, 50, null);
        $this->assertEquals("zor@gmail.com", $snippets->first());
    }

    public function testDoNotTruncateTailWithEmptyIndicator()
    {
        $snippets = Snippet::extractEx(["zor"], "zor@gmail.com", 3, 50, "");
        $this->assertEquals("zor@gmail.com", $snippets->first());
    }

    private function text(): string
    {
        return "Welcome to Yahoo!, the world’s most visited home page. Quickly find what you’re searching for, get in touch with friends and stay in-the-know with the latest news and information. CloudSponge provides an interface to easily enable your users to import contacts from a variety of the most popular webmail services including Yahoo, Gmail and Hotmail/MSN as well as popular desktop address books such as Mac Address Book and Outlook.";
    }
}
