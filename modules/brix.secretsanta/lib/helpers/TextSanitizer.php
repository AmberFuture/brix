<?php
namespace Brix\SecretSanta\Helpers;

use Bitrix\Main\Text\Emoji;
use CBXSanitizer;
use CSmile;
use CSmileGallery;

/**
 * Class TextSanitizer
 * 
 * @package Brix\SecretSanta\Helpers
 */
final class TextSanitizer
{
    protected bool $isMobile;
    protected string $text = "";
    protected string $mobileTextView = "";
    protected string $mobileTextEdit = "";
    protected string $pathToSmile = "";
    protected string $wordSeparator = "\\s.,;:!?\\#\\-\\*\\|\\[\\]\\(\\)\\{\\}";
    protected int $smilesGallery = CSmileGallery::GALLERY_DEFAULT;
    protected array $preg = [];
    protected array $tags = [
        "a" => ["href", "target", "style"],
        "b" => ["style"],
        "br" => [],
        "span" => ["style"],
        "ul" => ["style"],
        "ol" => ["style"],
        "li" => ["style"],
        "img" => ["src", "title", "id", "style", "border", "data-bx-image", "data-bx-onload"]
    ];
    protected array $smiles = [];
    protected array $repoSmiles = [];
    protected array $smilePatterns = [];
    protected array $smileReplaces = [];

    /**
     * Class Constructor
     * 
     * @param string $text
     */
    public function __construct(string $text, bool $isMobile = false)
    {
        $this->text = $text;
        $this->isMobile = $isMobile;
    }

    /**
     * Cleans text from prohibited html tags and converts emojis and links
     * 
     * @return void
     */
    public function sanitizer(): void
    {
        $sanitizer = new CBXSanitizer();
        $sanitizer->AddTags($this->tags);
        $this->text = $sanitizer->SanitizeHtml($this->text);

        if ($this->isMobile) {
            $this->specialReplaces();
            $this->mobileTextEdit = $this->text;
            $this->text = preg_replace_callback('/\r\n|\r|\n/', function($matches) {
                return "<br>";
            }, $this->text);
            $this->linksConvert();
            $this->specialReplaces();
            $this->mobileTextView = $this->text;
        }
        
        $this->convertSmiles();
        $this->specialReplaces();
    }
    
    /**
     * Clears the text from excess for mobile output
     */
    public function sanitizerMobile(): void
    {
        $this->initSmiles();
        $this->mobileTextView = $this->text;
        $this->mobileTextEdit = $this->text;

        if (!empty($this->text)) {
            # Replacing emoji images with their codes
            if ($this->smiles) {
                $smiles = [];

                foreach ($this->smiles as $smile) {
                    if (!array_key_exists($smile["IMAGE"], $smiles)) {
                        $smiles[$smile["IMAGE"]] = $smile["TYPING"];
                    }
                }

                $this->text = preg_replace_callback('/<img[^>]*>/', function($images) use ($smiles) {
                    preg_match_all('/src="([^\\s]+)"/', $images[0], $src);
                    return (isset($src[1][0]) && isset($smiles[$src[1][0]])) ? $smiles[$src[1][0]] : $images[0];
                }, $this->text);
                $this->mobileTextView = $this->text;
            }

            # Replacing links to the text
            $this->text = preg_replace_callback('/<a [^>]+>.+?<\/a>/', function($links) {
                preg_match_all('/href="([^\\s]+)"/', $links[0], $href);
                return isset($href[1][0]) ? $href[1][0] : "";
            }, $this->text);

            # Replacing line breaks
            $this->text = preg_replace_callback('/<br\s*\/?>/i', function($matches) {
                return "\n";
            }, $this->text);
            $this->mobileTextEdit = $this->text;
        }
    }

    /**
     * Returns a text string
     * 
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * Returns the text to view in mobile
     * 
     * @return string
     */
    public function getMobileTextView(): string
    {
        return $this->mobileTextView;
    }

    /**
     * Returns the text to edit in mobile
     * 
     * @return string
     */
    public function getMobileTextEdit(): string
    {
        return $this->mobileTextEdit;
    }

    /**
     * Converts links from text to html
     */
    protected function linksConvert()
    {
        if (!empty($this->text)) {
            $this->text = preg_replace_callback('/https?:\/\/[^\s]+/', function($links) {
                $url = $links[0];
                return '<a href="' . $url . '" target="_blank">' . $url . '</a>';
            }, $this->text);
        }
    }


    /**
     * Replaces special characters
     * 
     * @return void
     */
    protected function specialReplaces(): void
    {
        $this->text = mb_ereg_replace("&amp;nbsp;", " ", $this->text);
        $this->text = mb_ereg_replace("&quot;", "\"", $this->text);
        $this->text = trim($this->text);
    }

    /**
     * Replacing emoticons with images
     * 
     * @return void
     */
    protected function convertSmiles(): void
    {
        $this->initSmiles();
        $this->initSmilePatterns();

        if (!empty($this->smilePatterns)) {
            $this->text = preg_replace_callback($this->smilePatterns, [$this, "checkEmoticon"], " " . $this->text . " ");
        }

        $this->checkHTMLSpecialCharacters();
        $this->text = Emoji::decode($this->text);
    }

    /**
     * Creating an array of emoticons
     * 
     * @return void
     */
    protected function initSmiles(): void
    {
        if (!array_key_exists($this->smilesGallery, $this->repoSmiles)) {
            $smiles = CSmile::getByGalleryId(CSmile::TYPE_SMILE, $this->smilesGallery);
            $arSmiles = [];

            foreach ($smiles as $smile) {
                $arTypings = explode(" ", $smile["TYPING"]);

                foreach ($arTypings as $typing) {
                    $arSmiles[] = array_merge($smile, [
                        "TYPING" => $typing,
                        "IMAGE"  => CSmile::PATH_TO_SMILE . $smile["SET_ID"] . "/" . $smile["IMAGE"],
                        "DESCRIPTION" => $smile["NAME"],
                        "DESCRIPTION_DECODE" => "Y",
                    ]);
                }
            }

            $this->repoSmiles[$this->smilesGallery] = $arSmiles;
        }

        $this->smiles = $this->repoSmiles[$this->smilesGallery];
    }

    /**
     * Creates and saves smiley replacement patterns
     * 
     * @return void
     */
    protected function initSmilePatterns(): void
    {
        $pre = "";

        foreach ($this->smiles as $row) {
            if (preg_match("/\\w\$/", $row["TYPING"])) {
                $pre .= "|" . preg_quote($row["TYPING"], "/");
            }
        }
        
        foreach ($this->smiles as $row) {
            if (!empty($row["TYPING"]) && !empty($row["IMAGE"])) {
                $code = str_replace(["'", "<", ">"], ["\\'", "&lt;", "&gt;"], $row["TYPING"]);
                $patt = preg_quote($code, "/");
                $code = preg_quote(str_replace(["\x5C"], ["&#092;"], $code));
                $image = preg_quote(str_replace("'", "\\'", $row["IMAGE"]));
                $description = preg_quote(htmlspecialcharsbx(str_replace(["\x5C"], ["&#092;"], $row["DESCRIPTION"]), ENT_QUOTES), "/");
                $patternName = "pattern" . count($this->smilePatterns);
                $this->smilePatterns[] = "/(?<=^|\\>|[" . $this->wordSeparator . "\\&]" . $pre . ")(?P<" . $patternName . ">$patt)(?=$|\\<|[" . $this->wordSeparator . "\\&])/su";
                $this->smileReplaces[$patternName] = [
                    "code" => $code,
                    "image" => $image,
                    "description" => $description,
                    "width" => intval($row["IMAGE_WIDTH"]),
                    "height" => intval($row["IMAGE_HEIGHT"]),
                    "descriptionDecode" => $row["DESCRIPTION_DECODE"] == "Y",
                    "imageDefinition" => $row["IMAGE_DEFINITION"] ?: CSmile::IMAGE_SD,
                ];
            }
        }

        usort($this->smilePatterns, function($a, $b) {
            return (mb_strlen($a) > mb_strlen($b) ? -1 : 1);
        });
    }
    
    /**
     * Checks if the conversion is necessary
     * 
     * @param array $matches
     * @return string
     */
    protected function checkEmoticon(array $matches = []): string
    {
        $array = array_intersect_key($this->smileReplaces, $matches);
        $replacement = reset($array);

        if (!empty($replacement)) {
            return $this->convertEmoticon(
                $replacement["code"],
                $replacement["image"],
                $replacement["description"],
                $replacement["width"],
                $replacement["height"],
                $replacement["descriptionDecode"],
                $replacement["imageDefinition"]
            );
        }

        return $matches[0];
    }

    /**
     * Converts emoticons
     * 
     * @param string $code
     * @param string $image
     * @param string $description
     * @param string $width
     * @param string $height
     * @param bool $descriptionDecode
     * @param string $imageDefinition
     * @return string
     */
    protected function convertEmoticon(
        string $code = "",
        string $image = "",
        string $description = "",
        string $width = "",
        string $height = "",
        bool $descriptionDecode = false,
        string $imageDefinition = CSmile::IMAGE_SD
    ): string
    {
        if ($code == "" || $image == "") {
            return "";
        }

        $code = stripslashes($code);
        $description = stripslashes($description);
        $image = stripslashes($image);
        $width = intval($width);
        $height = intval($height);

        if ($descriptionDecode) {
            $description = htmlspecialcharsback($description);
        }
        
        $html = '<img src="' . $this->pathToSmile . $image . '"'
        . ' border="0"'
        . ' data-code="' . $code . '"'
        . ' data-definition="' . $imageDefinition . '"'
        . ' alt="' . $code . '"'
        . ' style="' . ($width > 0 ? 'width:' . $width . 'px;' : '') . ($height > 0 ? 'height:' . $height . 'px;' : '') . '"'
        . ' title="' . $description . '" />';
        $cacheKey = md5($html);

        if (!isset($this->preg[$cacheKey])) {
            $this->preg[$cacheKey] = $html;
        }
        
        return $this->preg[$cacheKey];
    }

    /**
     * Checks and replaces the HTML representation of special characters
     * 
     * @return void
     */
    protected function checkHTMLSpecialCharacters(): void
    {
        $this->text = mb_ereg_replace("&amp;#", "&#", $this->text);
    }
}
