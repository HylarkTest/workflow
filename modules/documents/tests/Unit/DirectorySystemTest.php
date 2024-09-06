<?php

declare(strict_types=1);

namespace Tests\Documents\Unit;

use Documents\Core\FileType;
use Tests\Documents\TestCase;
use Documents\Models\Document;

class DirectorySystemTest extends TestCase
{
    /*
     * Documents can be grouped by folder
     *
     * @test
     */
    //    public function documents_can_be_grouped_by_folder()
    //    {
    //        $documents = [
    //            factory(Document::class)->create(['filename' => 'Documents/Images/landscape.jpg']),
    //            factory(Document::class)->create(['filename' => 'Documents/Images/portrait.jpg']),
    //            factory(Document::class)->create(['filename' => 'Documents/CV.docx']),
    //            factory(Document::class)->create(['filename' => 'Documents/booking_ref.pdf']),
    //            factory(Document::class)->create(['filename' => 'Documents/Properties/123_street.pdf']),
    //            factory(Document::class)->create(['filename' => 'Documents/Properties/35b_granville.pdf']),
    //            factory(Document::class)->create(['filename' => 'Documents/Properties/little_cottage.pdf']),
    //            factory(Document::class)->create(['filename' => 'Documents/Properties/456_millview.pdf']),
    //            factory(Document::class)->create(['filename' => 'dummy.txt']),
    //            factory(Document::class)->create(['filename' => 'intro.mp4']),
    //        ];
    //
    //        $structure = Document::query()
    //            ->in('/')->get();
    //
    //        $this->assertCount(3, $structure);
    //        $this->assertEquals('Documents', $structure->first()->name);
    //        $this->assertTrue($structure->type === FileType::DIRECTORY);
    //
    //        $this->assertEquals('dummy.txt', $structure->first()->name);
    //        $this->assertTrue($structure->type === FileType::FILE);
    //
    //        $this->assertEquals('intro.mp4', $structure->first()->name);
    //        $this->assertTrue($structure->type === FileType::FILE);
    //    }
}
