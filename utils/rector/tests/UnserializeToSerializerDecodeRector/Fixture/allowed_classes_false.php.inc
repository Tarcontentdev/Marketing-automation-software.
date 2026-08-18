<?php

namespace Utils\Rector\Tests\UnserializeToSerializerDecodeRector\Fixture;

class AllowedClassesFalse
{
    public function run(string $data)
    {
        return unserialize($data, ['allowed_classes' => false]);
    }
}

?>
-----
<?php

namespace Utils\Rector\Tests\UnserializeToSerializerDecodeRector\Fixture;

class AllowedClassesFalse
{
    public function run(string $data)
    {
        return \MailVotech\CoreBundle\Helper\Serializer::decode($data);
    }
}

?>
