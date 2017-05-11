<?php
namespace smy;

// 字节对象
//  [4个字节、表示消息主体长度][1个空字节]
class Byte {
    private $length = 0;
    private $byte   = '';

    public function __construct($string) {
        $this->writeChar($string);
    }

    public function getByte() {
        return $this->getProtecByte();
    }

    public function getLength() {
        return $this->length;
    }

    // 写入字符串
    private function writeChar($string) {
        // 获取总长度
        $this->length += strlen($string);
        // 将字符串转成相应的ASCII码
        $str = array_map('ord', str_split($string));
        // 打包
        foreach ($str as $vo) {
            $this->byte .= pack('c', $vo);
        }
    }

    private function writeInt($intVal) {
        $this->length += 4;
        $this->byte .= pack('L', $intVal);
    }

    private function writeShortInt($interge) {
        $this->length += 2;
        $this->byte .= pack('v', $interge);
    }

    private function getProtecByte() {
        $lenByte = pack('N', $this->length);
        // 生成个空字节
        $nullByte = pack('c', '0');
        return $lenByte.$nullByte.$this->byte;
    }
}
?>
