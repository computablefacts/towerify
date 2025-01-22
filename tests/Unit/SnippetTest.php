<?php

namespace Tests\Unit;

use App\Helpers\Snippet;
use Tests\TestCaseNoDb;

class SnippetTest extends TestCaseNoDb
{
    public function testSearchedWordsNotInText()
    {
        $snippet = Snippet::extract(["not_here"], "Hello world!");
        $this->assertEquals("Hello world!", $snippet);
    }

    public function testTextShorterThanSnippetMaxLength()
    {
        $snippet = Snippet::extract(["world"], "Hello world!");
        $this->assertEquals("Hello world!", $snippet);
    }

    public function testSnippetWithLeftEllipsis()
    {
        $snippet = Snippet::extract(["Yahoo", "Outlook"], $this->text());
        $this->assertEquals("...in-the-know with the latest news and information. CloudSponge provides an interface to easily enable your users to import contacts from a variety of the most popular webmail services including Yahoo, Gmail and Hotmail/MSN as well as popular desktop address books such as Mac Address Book and Outlook.", $snippet);
    }

    public function testSnippetWithoutLeftEllipsis()
    {
        $snippet = Snippet::extractEx(["Yahoo", "Outlook"], $this->text(), 300, 50, "");
        $this->assertEquals("in-the-know with the latest news and information. CloudSponge provides an interface to easily enable your users to import contacts from a variety of the most popular webmail services including Yahoo, Gmail and Hotmail/MSN as well as popular desktop address books such as Mac Address Book and Outlook.", $snippet);
    }

    public function testSnippetWithRightEllipsis()
    {
        $snippet = Snippet::extract(["most", "visited", "home", "page"], $this->text());
        $this->assertEquals("Welcome to Yahoo!, the world’s most visited home page. Quickly find what you’re searching for, get in touch with friends and stay in-the-know with the latest news and information. CloudSponge provides an interface to easily enable your users to import contacts from a variety of the most popular webmail...", $snippet);
    }

    public function testSnippetWithLeftAndRightEllipsis()
    {
        $snippet = Snippet::extract(["latest", "news", "CloudSponge"], $this->text());
        $this->assertEquals("...in touch with friends and stay in-the-know with the latest news and information. CloudSponge provides an interface to easily enable your users to import contacts from a variety of the most popular webmail services including Yahoo, Gmail and Hotmail/MSN as well as popular desktop address books such as Mac...", $snippet);
    }

    public function testSnippetWithEmptyIndicator()
    {
        $snippet = Snippet::extractEx(["latest", "news", "CloudSponge"], $this->text(), 300, 50, "");
        $this->assertEquals("in touch with friends and stay in-the-know with the latest news and information. CloudSponge provides an interface to easily enable your users to import contacts from a variety of the most popular webmail services including Yahoo, Gmail and Hotmail/MSN as well as popular desktop address books such as Mac", $snippet);
    }

    public function testSnippetWithNullIndicator()
    {
        $snippet = Snippet::extractEx(["latest", "news", "CloudSponge"], $this->text(), 300, 50, null);
        $this->assertEquals("...in touch with friends and stay in-the-know with the latest news and information. CloudSponge provides an interface to easily enable your users to import contacts from a variety of the most popular webmail services including Yahoo, Gmail and Hotmail/MSN as well as popular desktop address books such as Mac...", $snippet);
    }

    public function testDoNotTruncateHead()
    {
        $snippet = Snippet::extractEx(["gmail"], "zor@gmail.com", 5, 0, "...");
        $this->assertEquals("...gmail...", $snippet);
    }

    public function testDoNotTruncateHeadWithNullIndicator()
    {
        $snippet = Snippet::extractEx(["gmail"], "zor@gmail.com", 5, 0, null);
        $this->assertEquals("...gmail...", $snippet);
    }

    public function testDoNotTruncateHeadWithEmptyIndicator()
    {
        $snippet = Snippet::extractEx(["gmail"], "zor@gmail.com", 5, 0, "");
        $this->assertEquals("gmail", $snippet);
    }

    public function testDoNotTruncateTail()
    {
        $snippet = Snippet::extractEx(["zor"], "zor@gmail.com", 3, 50, "...");
        $this->assertEquals("zor@gmail.com", $snippet);
    }

    public function testDoNotTruncateTailWithNullIndicator()
    {
        $snippet = Snippet::extractEx(["zor"], "zor@gmail.com", 3, 50, null);
        $this->assertEquals("zor@gmail.com", $snippet);
    }

    public function testDoNotTruncateTailWithEmptyIndicator()
    {
        $snippet = Snippet::extractEx(["zor"], "zor@gmail.com", 3, 50, "");
        $this->assertEquals("zor@gmail.com", $snippet);
    }

    private function text(): string
    {
        return "Welcome to Yahoo!, the world’s most visited home page. Quickly find what you’re searching for, get in touch with friends and stay in-the-know with the latest news and information. CloudSponge provides an interface to easily enable your users to import contacts from a variety of the most popular webmail services including Yahoo, Gmail and Hotmail/MSN as well as popular desktop address books such as Mac Address Book and Outlook.";
    }
}
