<?php

namespace Utils\Rector\Tests\UnserializeToSerializerDecodeRector\Fixture;

class PlainUnserialize
{
    public function run(string $data)
    {
        return unserialize($data);
    }
}

?>
-----
<?php

namespace Utils\Rector\Tests\UnserializeToSerializerDecodeRector\Fixture;

class PlainUnserialize
{
    public function run(string $data)
    {
        return \MailVotech\CoreBundle\Helper\Serializer::decode($data);
    }
}

?>
