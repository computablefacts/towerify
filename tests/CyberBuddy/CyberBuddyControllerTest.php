<?php

namespace CyberBuddy;

use App\Modules\CyberBuddy\Http\Controllers\CyberBuddyController;
use Tests\TestCase;

class CyberBuddyControllerTest extends TestCase
{
    public function testItRemovesSourcesFromAnswer()
    {
        $html = CyberBuddyController::removeSourcesFromAnswer("La source est [[36]].");
        $this->assertEquals("La source est .", $html);

        $html = CyberBuddyController::removeSourcesFromAnswer("La source est [[36],[44]].");
        $this->assertEquals("La source est .", $html);
    }

    public function testItCreatesATooltipWhenASingleReferenceIsUsed()
    {
        $tooltip = "La source est \n                  <div class=\"tooltip\">\n                    <b style=\"color:#f8b500\">[36]</b>\n                    <span class=\"tooltiptext tooltip-top\">Source !</span>\n                  </div>\n                .";

        $html = CyberBuddyController::enhanceAnswerWithSources(
            "La source est [[36]].",
            collect([[
                'id' => '36', // string
                'text' => 'Source !',
            ]]));

        $this->assertEquals($tooltip, $html);

        $html = CyberBuddyController::enhanceAnswerWithSources(
            "La source est [[36]].",
            collect([[
                'id' => 36, // number
                'text' => 'Source !',
            ]]));

        $this->assertEquals($tooltip, $html);
    }

    public function testItCreatesATooltipWhenMultipleReferencesAreUsed()
    {
        $tooltip = "La source est \n                  <div class=\"tooltip\">\n                    <b style=\"color:#f8b500\">[36]</b>\n                    <span class=\"tooltiptext tooltip-top\">Source n°1</span>\n                  </div>\n                ,\n                  <div class=\"tooltip\">\n                    <b style=\"color:#f8b500\">[44]</b>\n                    <span class=\"tooltiptext tooltip-top\">Source n°2</span>\n                  </div>\n                .";

        $html = CyberBuddyController::enhanceAnswerWithSources(
            "La source est [[36],[44]].",
            collect([[
                'id' => '36', // string
                'text' => 'Source n°1',
            ], [
                'id' => 44, // number
                'text' => 'Source n°2',
            ]]));

        $this->assertEquals($tooltip, $html);
    }
}
