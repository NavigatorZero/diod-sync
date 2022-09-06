<?php

namespace Tests\Unit\Api;

use Illuminate\Console\OutputStyle;
use Tests\TestCase;

class Sima extends TestCase
{
    protected \App\Http\Api\Sima $sima;
    protected OutputStyle $outputStyle;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {

        $this->sima = new \App\Http\Api\Sima();
        $this->outputStyle = $this->getMockBuilder(OutputStyle::class);

            $this->createMock(OutputStyle::class)
            ->method("progressAdvance")
            ->willReturnCallback(fn()=>var_dump("horosho"))->get;
        parent::__construct($name, $data, $dataName);
    }

    public function test_get_items()
    {
        $this->assertNull($this->sima->getItems($this->outputStyle));
    }

}
