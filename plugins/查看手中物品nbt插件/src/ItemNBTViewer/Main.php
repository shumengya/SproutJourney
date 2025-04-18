<?php
namespace ItemNBTViewer;

use pocketmine\nbt\tag\ByteArrayTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntArrayTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\LongTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\Tag;
use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\item\Item;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\utils\TextFormat;
use pocketmine\Player;

class Main extends PluginBase {

    public function onEnable(): void {
        $this->getServer()->getLogger()->info(TextFormat::GREEN . "[物品NBT查看器] 插件激活成功!");
        // 创建NBT存储目录
        @mkdir($this->getDataFolder() . "nbts/", 0777, true);
    }



    public function onCommand(CommandSender $sender, Command $cmd, $label, array $args): bool {
        if(strtolower($cmd->getName()) === "viewnbt") {
            if(!$sender instanceof Player) {
                $sender->sendMessage(TextFormat::RED . "该命令只能在游戏内使用");
                return true;
            }

            $item = $sender->getInventory()->getItemInHand();

            if($item->getId() === Item::AIR) {
                $sender->sendMessage(TextFormat::RED . "你手上没有拿着任何物品");
                return true;
            }

            /** @var CompoundTag|null $nbt */
            $nbt = $item->getNamedTag();

            if($nbt === null){
                $sender->sendMessage(TextFormat::YELLOW . "该物品没有NBT数据");
                return true;
            }

            // 显示给玩家
            $this->displayNBTData($sender, $nbt);

            // 保存到文件
            $this->saveNBTToFile($nbt, $sender);

            return true;
        }
        return false;
    }


    private function saveNBTToFile(CompoundTag $nbt, CommandSender $sender): void {
        // 生成安全文件名
        $filename = $this->generateFilename($sender);
        $filePath = $this->getDataFolder() . "nbts/" . $filename;

        // 获取去色后的NBT数据
        $rawNBT = TextFormat::clean($this->formatNBT($nbt));

        // 写入文件
        file_put_contents($filePath, $rawNBT);

        if($sender instanceof Player){
            $sender->sendMessage(TextFormat::GREEN . "NBT已保存至: " . $filename);
        }
    }

    private function generateFilename(CommandSender $sender): string {
        // 文件名组成：玩家名_日期_随机数.txt
        $safeName = preg_replace("/[^a-zA-Z0-9]/", "_", $sender->getName());
        $timestamp = date("Ymd_His");
        $random = mt_rand(1000, 9999);

        return sprintf("%s_%s_%d.txt", $safeName, $timestamp, $random);
    }




    private function displayNBTData(Player $player, CompoundTag $nbt): void {
        $output = TextFormat::AQUA . "=== 物品NBT数据 ===\n";
        $output .= $this->formatNBT($nbt);
        $player->sendMessage($output);
    }

    private function formatNBT(Tag $tag, int $depth = 0): string {
        $indent = str_repeat("    ", $depth);
        $type = $this->getTagType($tag);

        switch(true){
            case $tag instanceof CompoundTag:
                $children = array_map(function($name, Tag $child) use ($depth) {
                    return $this->formatNBT($child, $depth + 1);
                }, array_keys($tag->getValue()), $tag->getValue());

                return TextFormat::GOLD . "new CompoundTag(\"\", [\n" .
                    $indent . "    " . implode(",\n" . $indent . "    ", $children) . "\n" .
                    $indent . "])";

            case $tag instanceof ListTag:
                $children = array_map(function($i, Tag $child) use ($depth) {
                    return $this->formatNBT($child, $depth + 1);
                }, array_keys($tag->getValue()), $tag->getValue());

                $listType = $tag->getTagType() !== NBT::TAG_End ?
                    ", " . $this->getTypeConstant($tag->getTagType()) : "";

                return TextFormat::GREEN . "new ListTag(\"\", [\n" .
                    $indent . "    " . implode(",\n" . $indent . "    ", $children) . "\n" .
                    $indent . "]" . $listType . ")";

            case $tag instanceof ByteArrayTag:
                $hex = bin2hex($tag->getValue());
                return TextFormat::BLUE . "new ByteArrayTag(\"\", \"" . $hex . "\")";

            case $tag instanceof IntArrayTag:
                $values = $tag->getValue();
                return TextFormat::DARK_AQUA . "new IntArrayTag(\"\", [" . implode(", ", $values) . "])";

            default:
                $value = $this->formatValue($tag->getValue());
                return TextFormat::WHITE . "new " . $type . "(\"\", " . $value . ")";
        }
    }

    private function getTagType(Tag $tag): string {
        $types = [
            ByteTag::class => "ByteTag",
            ShortTag::class => "ShortTag",
            IntTag::class => "IntTag",
            LongTag::class => "LongTag",
            FloatTag::class => "FloatTag",
            DoubleTag::class => "DoubleTag",
            StringTag::class => "StringTag",
            // 其他类型...
        ];
        return $types[get_class($tag)] ?? "Tag";
    }

    private function formatValue($value): string {
        if(is_string($value)) {
            return "\"" . addslashes($value) . "\"";
        }
        if(is_bool($value)) {
            return $value ? "true" : "false";
        }
        return (string)$value;
    }

    private function getTypeConstant(int $type): string {
        $constants = [
            NBT::TAG_Byte => "NBT::TAG_Byte",
            NBT::TAG_Short => "NBT::TAG_Short",
            NBT::TAG_Int => "NBT::TAG_Int",
            NBT::TAG_Long => "NBT::TAG_Long",
            NBT::TAG_Float => "NBT::TAG_Float",
            NBT::TAG_Double => "NBT::TAG_Double",
            NBT::TAG_String => "NBT::TAG_String",
            NBT::TAG_Compound => "NBT::TAG_Compound",
            NBT::TAG_List => "NBT::TAG_List",
            NBT::TAG_ByteArray => "NBT::TAG_ByteArray",
            NBT::TAG_IntArray => "NBT::TAG_IntArray"
        ];
        return $constants[$type] ?? "NBT::TAG_End";
    }

}
