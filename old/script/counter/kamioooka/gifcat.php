<?php
/*

gifcat.php By TOMO

Version 1.1(2002/07/29) based on gifcat.pl Ver.1.61


注1) v1.1からファイル名が gifcat.phps から gifcat.php に変わってます。

■ 概要

とほほさん( http://tohoho.wakusei.ne.jp/ )の
PERL用GIF画像連結ライブラリgifcat.plのPHP版です。
Ver1.61を元にPHP用クラスとして作成しました。
基本的に機能、制限事項などgifcat.plに準じます。
ちなみに私自身は処理内容を全く理解してません!! (^^;
強引かつ機械的にPHPで動くようにしただけなんです。
ちなみにとほほさんには公開の許可を頂いています。


■ 利用について

利用、改編、再配布などご自由に。特に制限はありません。
不安な人は私までメール下さい。
基本的に著作権はとほほさんに帰属すると思います。
私はPHPな部分だけです。よくわからないです(^^;


■ 基本的な使い方


<?php
//このファイルを読み込む
require("./gifcat.php");

//国際化版で失敗しないために(私はこれに気づかず半日潰しました(T_T))
if (function_exists("i18n_http_output")) i18n_http_output("pass");

//新規オブジェクトを作成(クラス名は"gifcat"です)
$gifcat = new gifcat;

//画像ファイル名(パス)を配列に格納
$images = array("image1.gif", "image2.gif", "image3.gif");

//GIF用のHTTPヘッダを送信
header("Content-Type: image/gif");

//連結画像を出力(出力関数名は"output()"です)
echo @$gifcat->output($images);
    //変数の初期化をちゃんとしてないのでWarningが出ないように
    //"@"を付けておくと良いです
?>
*/

class gifcat
{

	var $var;

	function gifcat()
	{
		$this->var['pflag'] = 0;
	}

	function gifprint($_files)
	{
		$this->var['pflag'] = 1;
		$this->output($_files);
		$this->var['pflag'] = 0;
	}

# =====================================================
# gifcat'gifcat() - get a concatenated GIF image.
# =====================================================
	function output($_files)
	{

		$this->var['Gif'] = 0;
		$this->var['leftpos'] = 0;
		$this->var['logicalScreenWidth'] = 0;
		$this->var['logicalScreenHeight'] = 0;
		$this->var['useLocalColorTable'] = 0;

		$_num = count($_files);

		for ($_j = 0; $_j < $_num; ++$_j) {

			$_file = $_files[$_j];
			$_size = filesize($_file);
			$_fp   = fopen($_file, "rb");
			$this->var['buf']  = fread($_fp, $_size);
			fclose($_fp);

			$this->var['cnt'] = 0;
			$this->GifHeader();
			while (1) {
				$this->var['x1'] = ord(substr($this->var['buf'], $this->var['cnt'], 1));
				if ($this->var['x1'] == 0x2c) {
					$this->ImageBlock();
				} elseif ($this->var['x1'] == 0x21) {
					$this->var['x2'] = ord(substr($this->var['buf'], $this->var['cnt'] + 1, 1));
					if ($this->var['x2'] == 0xf9) {
						$this->GraphicControlExtension();
					} elseif ($this->var['x2'] == 0xfe) {
						$this->CommentExtension();
					} elseif ($this->var['x2'] == 0x01) {
						$this->PlainTextExtension();
					} elseif ($this->var['x2'] == 0xff) {
						$this->ApplicationExtension();
					} else {
						return("ERROR");
					}
				} elseif ($this->var['x1'] == 0x3b) {
					$this->Trailer();
					break;
				} elseif ($this->var['cnt'] == $this->var['size']) {
					break;
				} else {
					return("ERROR");
				}
			}

			$this->var['Gif']++;
		}
		if ($this->var['pflag'] == 1) {
			return;
		}

		$this->var['GifImage'] = "GIF89a";
		$this->var['GifImage'] .= pack("C", $this->var['logicalScreenWidth'] & 0x00ff);
		$this->var['GifImage'] .= pack("C", ($this->var['logicalScreenWidth'] & 0xff00) >> 8);
		$this->var['GifImage'] .= pack("C", $this->var['logicalScreenHeight'] & 0x00ff);
		$this->var['GifImage'] .= pack("C", ($this->var['logicalScreenHeight'] & 0xff00) >> 8);
		if ($this->var['useLocalColorTable']) {
			$this->var['PackedFields18'][0] &= ~0x80;
		}
		$this->var['GifImage'] .= pack("C", $this->var['PackedFields18'][0]);
		$this->var['GifImage'] .= pack("C", $this->var['BackgroundColorIndex']);
		$this->var['GifImage'] .= pack("C", $this->var['PixelAspectRatio']);
		if ($this->var['useLocalColorTable'] == 0) {
			$this->var['GifImage'] .= $this->var['globalColorTable'][0];
		}
		for ($_i = -1; $_i < $this->var['Gif']; $_i++) {
			$_j = ($_i == -1) ? 0 : $_i;
			$this->var['GifImage'] .= pack("CCC", 0x21, 0xf9, 0x04);
			$this->var['GifImage'] .= pack("C", $this->var['PackedFields23'] | $this->var['TransparentColorFlag'][$_j]);
			$this->var['GifImage'] .= pack("CC", 0x00, 0x00);
			$this->var['GifImage'] .= pack("C", $this->var['TransparentColorIndex'][$_j]);
			$this->var['GifImage'] .= pack("C", 0x00);
			$this->var['GifImage'] .= pack("C", 0x2c);
			$this->var['n'] = $this->var['leftpos'];
			$this->var['leftpos'] += ($_i == -1) ? 0 : $this->var['ImageWidth'][$_j];
			$this->var['GifImage'] .= pack("C", $this->var['n'] & 0x00ff);
			$this->var['GifImage'] .= pack("C", ($this->var['n'] & 0xff00) >> 8);
			$this->var['GifImage'] .= pack("CC", 0x00, 0x00);
			$this->var['GifImage'] .= pack("C", $this->var['ImageWidth'][$_j] & 0x00ff);
			$this->var['GifImage'] .= pack("C", ($this->var['ImageWidth'][$_j] & 0xff00) >> 8);
			$this->var['GifImage'] .= pack("C", $this->var['ImageHeight'] & 0x00ff);
			$this->var['GifImage'] .= pack("C", ($this->var['ImageHeight'] & 0xff00) >> 8);
			if ($this->var['useLocalColorTable']) {
				$this->var['PackedFields20'][$_j] |= 0x80;
				$this->var['PackedFields20'][$_j] &= ~0x07;
				$this->var['PackedFields20'][$_j] |= ($this->var['PackedFields18'][$_j] & 0x07);
				$this->var['GifImage'] .= pack("C", $this->var['PackedFields20'][$_j]);
				$this->var['GifImage'] .= $this->var['globalColorTable'][$_j];
			} else {
				$this->var['GifImage'] .= pack("C", $this->var['PackedFields20'][$_j]);
			}
			$this->var['GifImage'] .= pack("C", $this->var['LzwMinimumCodeSize'][$_j]);
			$this->var['GifImage'] .= $this->var['ImageData'][$_j];
		}
		$this->var['GifImage'] .= pack("C", 0x3b);

		return $this->var['GifImage'];

	}

# =====================================
# GifHeader
# =====================================
	function GifHeader() {
		$this->var['Signature'] = substr($this->var['buf'], $this->var['cnt'], 3); $this->var['cnt'] += 3;
		$this->var['Version']   = substr($this->var['buf'], $this->var['cnt'], 3); $this->var['cnt'] += 3;
		$this->var['LogicalScreenWidth']
				= ord(substr($this->var['buf'], $this->var['cnt'] + 0, 1))
				+ ord(substr($this->var['buf'], $this->var['cnt'] + 1, 1)) * 256; $this->var['cnt'] += 2;
		$this->var['LogicalScreenHeight']
				= ord(substr($this->var['buf'], $this->var['cnt'] + 0, 1))
				+ ord(substr($this->var['buf'], $this->var['cnt'] + 1, 1)) * 256; $this->var['cnt'] += 2;
		$this->var['PackedFields18'][$this->var['Gif']]   = ord(substr($this->var['buf'], $this->var['cnt'], 1)); $this->var['cnt']++;
		$this->var['GlobalColorTableFlag']   = ($this->var['PackedFields18'][$this->var['Gif']] & 0x80) >> 7;
		$this->var['ColorResolution']        = (($this->var['PackedFields18'][$this->var['Gif']] & 0x70) >> 4) + 1;
		$this->var['SortFlag']               = ($this->var['PackedFields18'][$this->var['Gif']] & 0x08) >> 3;
		$this->var['SizeOfGlobalColorTable'] = pow(2, (($this->var['PackedFields18'][$this->var['Gif']] & 0x07) + 1));
		$this->var['BackgroundColorIndex']   = ord(substr($this->var['buf'], $this->var['cnt'], 1)); $this->var['cnt']++;
		$this->var['PixelAspectRatio']       = ord(substr($this->var['buf'], $this->var['cnt'], 1)); $this->var['cnt']++;
		if ($this->var['GlobalColorTableFlag']) {
			$this->var['GlobalColorTable'] 
				= substr($this->var['buf'], $this->var['cnt'], $this->var['SizeOfGlobalColorTable'] * 3);
			$this->var['cnt'] += $this->var['SizeOfGlobalColorTable'] * 3;
		} else {
			$this->var['GlobalColorTable'] = "";
		}

		$this->var['logicalScreenWidth'] += $this->var['LogicalScreenWidth'];
		if ($this->var['logicalScreenHeight'] < $this->var['LogicalScreenHeight']) {
			$this->var['logicalScreenHeight'] = $this->var['LogicalScreenHeight'];
		}
		if ($this->var['GlobalColorTableFlag']) {
			$this->var['globalColorTable'][$this->var['Gif']] = $this->var['GlobalColorTable'];
			if ($this->var['Gif'] > 0) {
				if ($this->var['GlobalColorTable'] != $this->var['globalColorTable'][$this->var['Gif'] - 1]) {
					$this->var['useLocalColorTable'] = 1;
				}
			}
		}

		if ($this->var['pflag']) {
			printf("=====================================\n");
			printf("GifHeader\n");
			printf("=====================================\n");
			printf("Signature:                     %s\n", $this->var['Signature']);
			printf("Version:                       %s\n", $this->var['Version']);
			printf("Logical Screen Width:          %d\n", $this->var['LogicalScreenWidth']);
			printf("Logical Screen Height:         %d\n", $this->var['LogicalScreenHeight']);
			printf("Global Color Table Flag:       %d\n", $this->var['GlobalColorTableFlag']);
			printf("Color Resolution:              %d\n", $this->var['ColorResolution']);
			printf("Sort Flag:                     %d\n", $this->var['SortFlag']);
			printf("Size of Global Color Table:    %d * 3\n", $this->var['SizeOfGlobalColorTable']);
			printf("Background Color Index:        %d\n", $this->var['BackgroundColorIndex']);
			printf("Pixel Aspect Ratio:            %d\n", $this->var['PixelAspectRatio']);
			printf("Global Color Table:            \n");
			$this->Dump($this->var['GlobalColorTable']);
		}
	}

# =====================================
# Image Block
# =====================================
	function ImageBlock() {
		$this->var['ImageSeparator']    = ord(substr($this->var['buf'], $this->var['cnt'], 1)); $this->var['cnt']++;
		$this->var['ImageLeftPosition'] = ord(substr($this->var['buf'], $this->var['cnt'], 1))
				   + ord(substr($this->var['buf'], $this->var['cnt'] + 1, 1)) * 256; $this->var['cnt'] += 2;
		$this->var['ImageTopPosition']  = ord(substr($this->var['buf'], $this->var['cnt'], 1))
				   + ord(substr($this->var['buf'], $this->var['cnt'] + 1, 1)) * 256; $this->var['cnt'] += 2;
		$this->var['ImageWidth'][$this->var['Gif']]  = ord(substr($this->var['buf'], $this->var['cnt'], 1))
				   + ord(substr($this->var['buf'], $this->var['cnt'] + 1, 1)) * 256; $this->var['cnt'] += 2;
		$this->var['ImageHeight']       = ord(substr($this->var['buf'], $this->var['cnt'], 1))
				   + ord(substr($this->var['buf'], $this->var['cnt'] + 1, 1)) * 256; $this->var['cnt'] += 2;
		$this->var['PackedFields20'][$this->var['Gif']]  = ord(substr($this->var['buf'], $this->var['cnt'], 1)); $this->var['cnt']++;
		$this->var['LocalColorTableFlag']   = ($this->var['PackedFields20'][$this->var['Gif']] & 0x80) >> 7;
		$this->var['InterlaceFlag']         = ($this->var['PackedFields20'][$this->var['Gif']] & 0x40) >> 6;
		$this->var['SortFlag']              = ($this->var['PackedFields20'][$this->var['Gif']] & 0x20) >> 5;
		$this->var['Reserved']              = ($this->var['PackedFields20'][$this->var['Gif']] & 0x18) >> 3;
		if ($this->var['LocalColorTableFlag']) {
			$this->var['SizeOfLocalColorTable'] = pow(2, (($this->var['PackedFields20'][$this->var['Gif']] & 0x07) + 1));
			$this->var['LocalColorTable'] = substr($this->var['buf'], $this->var['cnt'], $this->var['SizeOfLocalColorTable']);
			$this->var['cnt'] += $this->var['SizeOfLocalColorTable'] * 3;
		} else {
			$this->var['SizeOfLocalColorTable'] = 0;
			$this->var['LocalColorTable'] = "";
		}
		$this->var['LzwMinimumCodeSize'][$this->var['Gif']] = ord(substr($this->var['buf'], $this->var['cnt'], 1)); $this->var['cnt']++;
		$this->var['ImageData'][$this->var['Gif']] = $this->DataSubBlock();

		if ($this->var['pflag']) {
			printf("=====================================\n");
			printf("Image Block\n");
			printf("=====================================\n");
			printf("Image Separator:               0x%02x\n", $this->var['ImageSeparator']);
			printf("Image Left Position:           %d\n", $this->var['ImageLeftPosition']);
			printf("Image Top Position:            %d\n", $this->var['ImageTopPosition']);
			printf("Image Width:                   %d\n", $this->var['ImageWidth'][$this->var['Gif']]);
			printf("Image Height:                  %d\n", $this->var['ImageHeight']);
			printf("Local Color Table Flag:        %d\n", $this->var['LocalColorTableFlag']);
			printf("Interlace Flag:                %d\n", $this->var['InterlaceFlag']);
			printf("Sort Flag:                     %d\n", $this->var['SortFlag']);
			printf("Reserved:                      --\n");
			printf("Size of Local Color Table:     %d\n", $this->var['SizeOfLocalColorTable']);
			printf("Local Color Table:             \n");
			$this->Dump($this->var['LocalColorTable']);
			printf("LZW Minimum Code Size:         %d\n", $this->var['LzwMinimumCodeSize'][$this->var['Gif']]);
			printf("Image Data:                    \n");
			$this->Dump($this->var['ImageData'][$this->var['Gif']]);
			printf("Block Terminator:              0x00\n");
		}
	}

# =====================================
# Graphic Control Extension
# =====================================
	function GraphicControlExtension() {
		$this->var['ExtensionIntroducer']   = ord(substr($this->var['buf'], $this->var['cnt'], 1)); $this->var['cnt']++;
		$this->var['GraphicControlLabel']   = ord(substr($this->var['buf'], $this->var['cnt'], 1)); $this->var['cnt']++;
		$this->var['BlockSize']             = ord(substr($this->var['buf'], $this->var['cnt'], 1)); $this->var['cnt']++;
		$this->var['PackedFields23']        = ord(substr($this->var['buf'], $this->var['cnt'], 1)); $this->var['cnt']++;
		$this->var['Reserved']              = ($this->var['PackedFields23'] & 0xe0) >> 5;
		$this->var['DisposalMethod']        = ($this->var['PackedFields23'] & 0x1c) >> 5;
		$this->var['UserInputFlag']         = ($this->var['PackedFields23'] & 0x02) >> 1;
		$this->var['TransparentColorFlag'][$this->var['Gif']]  = $this->var['PackedFields23'] & 0x01;
		$this->var['DelayTime']             = ord(substr($this->var['buf'], $this->var['cnt'], 1))
					   + ord(substr($this->var['buf'], $this->var['cnt']+1, 1)) * 256; $this->var['cnt'] += 2;
		$this->var['TransparentColorIndex'][$this->var['Gif']] = ord(substr($this->var['buf'], $this->var['cnt'], 1)); $this->var['cnt']++;
		$this->var['BlockTerminator']       = ord(substr($this->var['buf'], $this->var['cnt'], 1)); $this->var['cnt']++;

		if ($this->var['pflag']) {
			printf("=====================================\n");
			printf("Graphic Control Extension\n");
			printf("=====================================\n");
			printf("Extension Introducer:          0x%02x\n", $this->var['ExtensionIntroducer']);
			printf("Graphic Control Label:         0x%02x\n", $this->var['GraphicControlLabel']);
			printf("Block Size:                    %d\n", $this->var['BlockSize']);
			printf("Reserved:                      --\n");
			printf("Disposal Method:               %d\n", $this->var['DisposalMethod']);
			printf("User Input Flag:               %d\n", $this->var['UserInputFlag']);
			printf("Transparent Color Flag:        %d\n", $this->var['TransparentColorFlag'][$this->var['Gif']]);
			printf("Delay Time:                    %d\n", $this->var['DelayTime']);
			printf("Transparent Color Index:       %d\n", $this->var['TransparentColorIndex'][$this->var['Gif']]);
			printf("Block Terminator:              0x00\n");
		}
	}

# =====================================
# Comment Extension
# =====================================
	function CommentExtension() {
		$this->var['ExtensionIntroducer']   = ord(substr($this->var['buf'], $this->var['cnt'], 1)); $this->var['cnt']++;
		$this->var['CommentLabel']          = ord(substr($this->var['buf'], $this->var['cnt'], 1)); $this->var['cnt']++;
		$this->DataSubBlock();

		if ($this->var['pflag']) {
			printf("=====================================\n");
			printf("Comment Extension\n");
			printf("=====================================\n");
			printf("Extension Introducer:          0x%02x\n", $this->var['ExtensionIntroducer']);
			printf("Comment Label:                 0x%02x\n", $this->var['CommentLabel']);
			printf("Comment Data:                  ...\n");
			printf("Block Terminator:              0x%02x\n", $this->var['BlockTerminator']);
		}
	}

# =====================================
# Plain Text Extension
# =====================================
	function PlainTextExtension() {
		$this->var['ExtensionIntroducer']  = ord(substr($this->var['buf'], $this->var['cnt'], 1)); $this->var['cnt']++;
		$this->var['PlainTextLabel']       = ord(substr($this->var['buf'], $this->var['cnt'], 1)); $this->var['cnt']++;
		$this->var['BlockSize']            = ord(substr($this->var['buf'], $this->var['cnt'], 1)); $this->var['cnt']++;
		$this->var['TextGridLeftPosition'] = ord(substr($this->var['buf'], $this->var['cnt'], 1))
					  + ord(substr($this->var['buf'], $this->var['cnt'] + 1, 1)) * 256; $this->var['cnt'] += 2;
		$this->var['TextGridTopPosition']  = ord(substr($this->var['buf'], $this->var['cnt'], 1))
					  + ord(substr($this->var['buf'], $this->var['cnt'] + 1, 1)) * 256; $this->var['cnt'] += 2;
		$this->var['TextGridWidth']        = ord(substr($this->var['buf'], $this->var['cnt'], 1))
					  + ord(substr($this->var['buf'], $this->var['cnt'] + 1, 1)) * 256; $this->var['cnt'] += 2;
		$this->var['TextGridHeight']       = ord(substr($this->var['buf'], $this->var['cnt'], 1))
					  + ord(substr($this->var['buf'], $this->var['cnt'] + 1, 1)) * 256; $this->var['cnt'] += 2;
		$this->var['CharacterCellWidth']   = ord(substr($this->var['buf'], $this->var['cnt'], 1)); $this->var['cnt']++;
		$this->var['CharacterCellHeight']  = ord(substr($this->var['buf'], $this->var['cnt'], 1)); $this->var['cnt']++;
		$this->var['TextForegroundColorIndex'] = ord(substr($this->var['buf'], $this->var['cnt'], 1)); $this->var['cnt']++;
		$this->var['TextBackgroundColorIndex'] = ord(substr($this->var['buf'], $this->var['cnt'], 1)); $this->var['cnt']++;
		$this->DataSubBlock();

		if ($this->var['pflag']) {
			printf("=====================================\n");
			printf("Plain Text Extension\n");
			printf("=====================================\n");
			printf("Extension Introducer:        0x%02x\n", $this->var['ExtensionIntroducer']);
			printf("Plain Text Label:            0x%02x\n", $this->var['PlainTextLabel']);
			printf("Block Size:                  0x%02x\n", $this->var['BlockSize']);
			printf("Text Grid Left Position:     %d\n", $this->var['TextGridLeftPosition']);
			printf("Text Grid Top Position:      %d\n", $this->var['TextGridTopPosition']);
			printf("Text Grid Width:             %d\n", $this->var['TextGridWidth']);
			printf("Text Grid Height:            %d\n", $this->var['TextGridHeight']);
			printf("Text Foreground Color Index: %d\n", $this->var['TextForegroundColorIndex']);
			printf("Text Background Color Index: %d\n", $this->var['TextBackgroundColorIndex']);
			printf("Plain Text Data:             ...\n");
			printf("Block Terminator:            0x00\n");
		}
	}

# =====================================
# Application Extension
# =====================================
	function ApplicationExtension() {
		$this->var['ExtensionIntroducer']           = ord(substr($this->var['buf'], $this->var['cnt'], 1)); $this->var['cnt']++;
		$this->var['ExtentionLabel']                = ord(substr($this->var['buf'], $this->var['cnt'], 1)); $this->var['cnt']++;
		$this->var['BlockSize']                     = ord(substr($this->var['buf'], $this->var['cnt'], 1)); $this->var['cnt']++;
		$this->var['ApplicationIdentifire']         = substr($this->var['buf'], $this->var['cnt'], 8); $this->var['cnt'] += 8;
		$this->var['ApplicationAuthenticationCode'] = substr($this->var['buf'], $this->var['cnt'], 3); $this->var['cnt'] += 3;
		$this->DataSubBlock();

		if ($this->var['pflag']) {
			printf("=====================================\n");
			printf("Application Extension\n");
			printf("=====================================\n");
			printf("Extension Introducer:          0x%02x\n",
				$this->var['ExtensionIntroducer']);
			printf("Extension Label:               0x%02x\n",
				$this->var['PlainTextLabel']);
			printf("Block Size:                    0x%02x\n",
				$this->var['BlockSize']);
			printf("Application Identifire:        ...\n");
			printf("ApplicationAuthenticationCode: ...\n");
			printf("Block Terminator:              0x00\n");
		}
	}

# =====================================
# Trailer
# =====================================
	function Trailer() {
		$this->var['cnt']++;

		if ($this->var['pflag']) {
			printf("=====================================\n");
			printf("Trailer\n");
			printf("=====================================\n");
			printf("Trailer:                       0x3b\n");
			printf("\n");
		}
	}

# =====================================
# Data Sub Block
# =====================================
	function DataSubBlock() {
		$_from = $this->var['cnt'];
		while ($_n = ord(substr($this->var['buf'], $this->var['cnt'], 1))) {
			$this->var['cnt']++;
			$this->var['cnt'] += $_n;
		}
		$this->var['cnt']++;
		return(substr($this->var['buf'], $_from, $this->var['cnt'] - $_from));
	}

# =====================================
# Memory Dump
# =====================================
	function Dump($_buf) {

		if (strlen($_buf) == 0) {
			return;
		}
		for ($_i = 0; $_i < strlen($_buf); $_i++) {
			if (($_i % 16) == 0) {
				printf("  ");
			}
			printf("%02X ", ord(substr($_buf, $_i, 1)));
			if (($_i % 16) == 15) {
				printf("\n");
			}
		}
		if (($_i % 16) != 0) {
			printf("\n");
		}
	}

}
?>
