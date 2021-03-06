<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace spec\Sylius\Component\Core\Uploader;

use Gaufrette\Filesystem;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sylius\Component\Core\Model\ImageInterface;
use Sylius\Component\Core\Uploader\ImageUploaderInterface;
use Symfony\Component\HttpFoundation\File\File;

final class ImageUploaderSpec extends ObjectBehavior
{
    function let(Filesystem $filesystem, ImageInterface $image): void
    {
        $filesystem->has(Argument::any())->willReturn(false);

        $file = new File(__FILE__);
        $image->getFile()->willReturn($file);

        $this->beConstructedWith($filesystem);
    }

    function it_is_an_image_uploader(): void
    {
        $this->shouldImplement(ImageUploaderInterface::class);
    }

    function it_uploads_an_image(Filesystem $filesystem, ImageInterface $image): void
    {
        $image->hasFile()->willReturn(true);
        $image->getPath()->willReturn('foo.jpg');

        $filesystem->has('foo.jpg')->willReturn(false);

        $filesystem->delete(Argument::any())->shouldNotBeCalled();

        $image->setPath(Argument::type('string'))->will(function ($args) use ($image, $filesystem) {
            $image->getPath()->willReturn($args[0]);

            $filesystem->write($args[0], Argument::any())->shouldBeCalled();
        })->shouldBeCalled();

        $this->upload($image);
    }

    function it_replaces_an_image(Filesystem $filesystem, ImageInterface $image): void
    {
        $image->hasFile()->willReturn(true);
        $image->getPath()->willReturn('foo.jpg');

        $filesystem->has('foo.jpg')->willReturn(true);

        $filesystem->delete('foo.jpg')->willReturn(true);

        $image->setPath(Argument::type('string'))->will(function ($args) use ($image, $filesystem) {
            $image->getPath()->willReturn($args[0]);

            $filesystem->write($args[0], Argument::any())->shouldBeCalled();
        })->shouldBeCalled();

        $this->upload($image);
    }

    function it_removes_an_image_if_exists(Filesystem $filesystem): void
    {
        $filesystem->has('path/to/img')->willReturn(true);
        $filesystem->delete('path/to/img')->willReturn(true);

        $this->remove('path/to/img');
    }

    function it_does_not_remove_an_image_if_does_not_exist(FileSystem $filesystem): void
    {
        $filesystem->has('path/to/img')->willReturn(false);
        $filesystem->delete('path/to/img')->shouldNotBeCalled();

        $this->remove('path/to/img');
    }
}
