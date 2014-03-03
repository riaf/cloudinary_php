<?php

require __DIR__ . '/../vendor/autoload.php';

use Cloudinary\Cloudinary;
use Cloudinary\Resource as CloudinaryResource;

class CloudinaryResourceTest extends \PHPUnit_Framework_TestCase
{
    protected $cloudinary;

    public function setUp()
    {
        $this->cloudinary = new CloudinaryResource('test123');
    }

    public function testGetUrl()
    {
        $this->assertEquals(
            'http://res.cloudinary.com/test123/image/facebook/sato.keisuke.png',
            $this->cloudinary->getUrl('sato.keisuke.png', array('type' => 'facebook'))
        );

        // basic
        $this->assertEquals(
            'http://res.cloudinary.com/test123/image/upload/Crocos.Inc.png',
            $this->cloudinary->getUrl('Crocos.Inc.png')
        );

        // facebook
        $this->assertEquals(
            'http://res.cloudinary.com/test123/image/facebook/c_crop,h_80,w_80/sato.keisuke.png',
            $this->cloudinary->getUrl('sato.keisuke.png', array(
                'type' => 'facebook',
                'width' => 80,
                'height' => 80,
                'crop' => 'crop',
            ))
        );

        // secure
        $this->assertEquals(
            'https://res.cloudinary.com/test123/image/upload/test',
            $this->cloudinary->getUrl('test', array(
                'secure' => true,
            ))
        );

        // overwrite secure distribution
        $this->assertEquals(
            'https://example.com/test123/image/upload/test',
            $this->cloudinary->getUrl('test', array(
                'secure' => true,
                'secure_distribution' => 'example.com',
            ))
        );

        // overwrite cname
        $this->assertEquals(
            'http://cdn.riaf.jp/test123/image/upload/test',
            $this->cloudinary->getUrl('test', array(
                'cname' => 'cdn.riaf.jp',
            ))
        );

        $cname = $this->cloudinary->getCname();
        $this->cloudinary->setCname('cdn2.riaf.jp');

        $this->assertEquals(
            'https://cdn2.riaf.jp/test123/image/upload/test',
            $this->cloudinary->getUrl('test', array(
                'secure' => true,
            ))
        );

        $this->cloudinary->setCname($cname);

        // format
        $this->assertEquals(
            'http://res.cloudinary.com/test123/image/upload/test.jpg',
            $this->cloudinary->getUrl('test', array(
                'format' => 'jpg',
            ))
        );

        // crop
        $this->assertEquals(
            'http://res.cloudinary.com/test123/image/upload/test',
            $this->cloudinary->getUrl('test', array(
                'width' => 100,
                'height' => 100,
            ))
        );
        $this->assertEquals(
            'http://res.cloudinary.com/test123/image/upload/c_crop,h_100,w_100/test',
            $this->cloudinary->getUrl('test', array(
                'width' => 100,
                'height' => 100,
                'crop' => 'crop',
            ))
        );

        // various options
        $this->assertEquals(
            'http://res.cloudinary.com/test123/image/upload/g_center,p_a,q_0.4,r_3,x_1,y_2/test',
            $this->cloudinary->getUrl('test',array(
                'x' => 1,
                'y' => 2,
                'radius' => 3,
                'gravity' => 'center',
                'quality' => 0.4,
                'prefix' => 'a',
            ))
        );

        // simple transformation
        $this->assertEquals(
            'http://res.cloudinary.com/test123/image/upload/t_blip/test',
            $this->cloudinary->getUrl('test', array(
                'transformation' => 'blip',
            ))
        );

        // array transformation
        $this->assertEquals(
            'http://res.cloudinary.com/test123/image/upload/t_blip.blop/test',
            $this->cloudinary->getUrl('test', array(
                'transformation' => array('blip', 'blop'),
            ))
        );

        // base transformation
        $this->assertEquals(
            'http://res.cloudinary.com/test123/image/upload/c_fill,x_100,y_100/c_crop,w_100/test',
            $this->cloudinary->getUrl('test', array(
                'transformation' => array(
                    'x' => 100,
                    'y' => 100,
                    'crop' => 'fill',
                ),
                'crop' => 'crop',
                'width' => 100,
            ))
        );

        // base array transformation
        $this->assertEquals(
            'http://res.cloudinary.com/test123/image/upload/c_fill,w_200,x_100,y_100/r_10/c_crop,w_100/test',
            $this->cloudinary->getUrl('test', array(
                'transformation' => array(
                    array(
                        'x' => 100,
                        'y' => 100,
                        'width' => 200,
                        'crop' => 'fill',
                    ),
                    array(
                        'radius' => 10,
                    ),
                ),
                'crop' => 'crop',
                'width' => 100,
            ))
        );

        // empty transformation
        $this->assertEquals(
            'http://res.cloudinary.com/test123/image/upload/c_fill,x_100,y_100/test',
            $this->cloudinary->getUrl('test', array(
                'transformation' => array(
                    array(),
                    array(
                        'x' => 100,
                        'y' => 100,
                        'crop' => 'fill',
                    ),
                    array(),
                )
            ))
        );

        // size
        $this->assertEquals(
            'http://res.cloudinary.com/test123/image/upload/c_crop,h_10,w_10/test',
            $this->cloudinary->getUrl('test', array(
                'size' => '10x10',
                'crop' => 'crop',
            ))
        );

        // resource type
        $this->assertEquals(
            'http://res.cloudinary.com/test123/raw/upload/test',
            $this->cloudinary->getUrl('test', array(
                'resource_type' => 'raw',
            ))
        );

        // ignore http
        $this->assertEquals(
            'http://example.com',
            $this->cloudinary->getUrl('http://example.com')
        );
        $this->assertEquals(
            'http://example.com',
            $this->cloudinary->getUrl('http://example.com', array(
                'type' => 'asset',
            ))
        );
        $this->assertEquals(
            'http://res.cloudinary.com/test123/image/fetch/http://example.com',
            $this->cloudinary->getUrl('http://example.com', array(
                'type' => 'fetch',
            ))
        );

        // fetch
        $this->assertEquals(
            'http://res.cloudinary.com/test123/image/fetch/http://example.com/hello%3Fa%3Db',
            $this->cloudinary->getUrl('http://example.com/hello?a=b', array(
                'type' => 'fetch',
            ))
        );

        // escape
        $this->assertEquals(
            'http://res.cloudinary.com/test123/image/youtube/http://www.youtube.com/watch%3Fv%3Dd9NF2edxy-M',
            $this->cloudinary->getUrl('http://www.youtube.com/watch?v=d9NF2edxy-M', array(
                'type' => 'youtube',
            ))
        );

        // background
        $this->assertEquals(
            'http://res.cloudinary.com/test123/image/upload/b_red/test',
            $this->cloudinary->getUrl('test', array(
                'background' => 'red',
            ))
        );
        $this->assertEquals(
            'http://res.cloudinary.com/test123/image/upload/b_rgb:112233/test',
            $this->cloudinary->getUrl('test', array(
                'background' => '#112233',
            ))
        );

        // default image
        $this->assertEquals(
            'http://res.cloudinary.com/test123/image/upload/d_default/test',
            $this->cloudinary->getUrl('test', array(
                'default_image' => 'default',
            ))
        );

        // angle
        $this->assertEquals(
            'http://res.cloudinary.com/test123/image/upload/a_12/test',
            $this->cloudinary->getUrl('test', array(
                'angle' => 12,
            ))
        );

        // overlay
        $this->assertEquals(
            'http://res.cloudinary.com/test123/image/upload/l_text:hello/test',
            $this->cloudinary->getUrl('test', array(
                'overlay' => 'text:hello',
            ))
        );
        $this->assertEquals(
            'http://res.cloudinary.com/test123/image/upload/h_100,l_text:hello,w_100/test',
            $this->cloudinary->getUrl('test', array(
                'overlay' => 'text:hello',
                'width' => 100,
                'height' => 100,
            ))
        );

        // underlay
        $this->assertEquals(
            'http://res.cloudinary.com/test123/image/upload/u_text:hello/test',
            $this->cloudinary->getUrl('test', array(
                'underlay' => 'text:hello',
            ))
        );
        $this->assertEquals(
            'http://res.cloudinary.com/test123/image/upload/h_100,u_text:hello,w_100/test',
            $this->cloudinary->getUrl('test', array(
                'underlay' => 'text:hello',
                'width' => 100,
                'height' => 100,
            ))
        );

        // fetch format
        $this->assertEquals(
            'http://res.cloudinary.com/test123/image/fetch/f_jpg/http://cloudinary.com/images/logo.png',
            $this->cloudinary->getUrl('http://cloudinary.com/images/logo.png', array(
                'format' => 'jpg',
                'type' => 'fetch',
            ))
        );

        // effect
        $this->assertEquals(
            'http://res.cloudinary.com/test123/image/upload/e_sepia/test',
            $this->cloudinary->getUrl('test', array(
                'effect' => 'sepia',
            ))
        );

        // effect with array
        $this->assertEquals(
            'http://res.cloudinary.com/test123/image/upload/e_sepia:10/test',
            $this->cloudinary->getUrl('test', array(
                'effect' => array('sepia', 10)
            ))
        );

        // density
        $this->assertEquals(
            'http://res.cloudinary.com/test123/image/upload/dn_150/test',
            $this->cloudinary->getUrl('test', array(
                'density' => 150,
            ))
        );

        // page
        $this->assertEquals(
            'http://res.cloudinary.com/test123/image/upload/pg_5/test',
            $this->cloudinary->getUrl('test', array(
                'page' => 5,
            ))
        );
    }
}
