<?php

    class ImageMaker {

        // arrest minority/majority
        public $fever;
        public $color_fever = 0xFAF;
        public $fatigue;
        public $color_fatigue = 0xF1D4F5;
        public $cough;
        public $color_cough = 0xFF0000;
        public $appetite;
        public $color_appetite = 0xFFF; 
        public $body_aches;
        public $color_body_aches = 0xFBBFF;
        public $breath;
        public $color_breath = 0xFFFAA;
        public $mucus;
        public $color_mucus = 0xFCEF;
        public $throat;
        public $color_throat = 0x9AFCB;
        public $headache;
        public $color_headache = 0xCBF09;
        public $chills;
        public $color_chills = 0xDEA82;
        public $smell;
        public $color_smell = 0xABC009;
        public $nose;
        public $color_nose = 0xFE0AFE;
        public $nausea;
        public $color_nausea = 0x9A1E19;
        public $diarrhea;
        public $color_diarrhea = 0xFF9119;
        public $asphixiation;
        public $color_asphixiation = 0xFEA929;
        public $chest_pressure;
        public $color_chest_pressure = 0xAFECB1;
        public $bluish;
        public $color_bluish_lips = 0xEACF29;
        public $confusion;
        public $color_confusion = 0x0A72D;
        public $symptom_total_pct;
        public $color;
        // Date of contraction
        public $date;
        // Image of contraction
        public $image;
        public $image_candle;
        // Candle height counter
        public $candle_cnt;
        // Days counted
        public $day_cnt = 7;
        // Image Scale
        public $image_scale = 0;
        public $image_width = 1000;
        public $image_height = 650;
        public $between = 50;

        public function start_image (string $img, array $arrays, array $totals)
        {
            $this->total = $totals;
            $this->arr = $arrays;
            $x = 0;
            $keys = [];
            foreach ($this->arr as $key => $values) {
                $keys[$key] = $values * $this->total[$x];
                $x++;
            }
            $this->image_scale = array_sum(array_values($keys))*100;
            $this->image_candle = imagecreatetruecolor(25, $this->image_scale);
            $this->candle_name = $img;
            $this->image = imagecreatetruecolor($this->image_width, $this->image_height);
            imagefilledrectangle($this->image, 0, 0, $this->image_width, $this->image_height, 0xDDDDDD);
            imagefilter($this->image, IMG_FILTER_GRAYSCALE);
            return $this;
        }

        public function draw_dashed_pct ($percent, $between = 0)
        {
            imagedashedline($this->image, 0, $percent%$this->image_scale, $this->image_width, $percent%$this->image_scale, 0x00);
            while ($percent < $this->image_height && $this->between > 0) {
                $percent += $this->between;
                imagedashedline($this->image, 0, $percent%$this->image_height, $this->image_width, $percent%$this->image_height, 0x00);
            }
            return $this;
        }

        public function set_symptom_color(string $symptom)
        {
            $color = 'color_' . $symptom;
            $this->color = $this->$color;
            return $this;
        }

        public function draw_candlestick (string $symptom, float $symptom_pct, float $total) {
            if ($total == 0)
                return $this;
            imageflip($this->image_candle, IMG_FLIP_VERTICAL);
            $this->set_symptom_color($symptom);
            imagefilledrectangle($this->image_candle, 0, ($this->symptom_total_pct)*100, 25, ($this->symptom_total_pct + ($symptom_pct* $total))*100, $this->color);
            $this->symptom_total_pct += $symptom_pct;
            return $this;
        }

        public function merge_candlestick (int $day_cnt, int $cases)
        {
            imagecopymerge($this->image, $this->image_candle, $day_cnt*50, $cases * ($this->between/$this->image_height), 0, 0, 25, $this->image_scale, 100);
            $this->symptom_total_pct = 0;
            return $this;
        }

        public function export ()
        {
            imageflip($this->image, IMG_FLIP_VERTICAL);
            $this->print_legend();
            imagewebp($this->image,$this->candle_name,100);
            imagedestroy($this->image);
        }

        public function print_legend()
        {
            
            $x = 0;
            imagefilledrectangle($this->image, 480, 200, 700, 550, 0xFFFFFF);
            foreach ($this->arr as $symptom => $percent)
            {
                $this->set_symptom_color($symptom);
                $x += 15;
                imagestring($this->image, 5, 500, 220+$x, "{$symptom}", $this->color);
            }
            return $this;
        }
    }
    $x = 0;
    $symptoms = array("fever" => 0.04, "fatigue" => 0.06, "cough" => 0.05, "appetite" => 0.09, "body_aches" => 0.11,
            "breath" => 0.09, "mucus" => 0.03, "throat" => 0.07, "headache" => 0.1, "chills" => 0.08, "smell" => 0.1, "nose" => 0.02, "nausea" => 0.14, "diarrhea" => 0.15,
            "asphixiation" => 0.25, "chest_pressure" => 0.25, "bluish_lips" => 0.40, "confusion" => 0.11);
    
    $total_symptoms = array(1, 2, 1, 2,1,2,1,1,1,1,1,2,1,2,2,1,1,2);
    $img_mrk = new ImageMaker();
    $img_mrk->start_image("null.webp", $symptoms, $total_symptoms)->draw_dashed_pct(50, 50);
    $y = 0;
    foreach ($symptoms as $symptom => $percent)
    {
        $img_mrk->draw_candlestick($symptom, $percent, $total_symptoms[$y]);
        $y++;
    }

    $img_mrk->merge_candlestick(1, 500);
    $img_mrk->merge_candlestick(2, 75);
    $img_mrk->merge_candlestick(3, 75);
    $img_mrk->export();

    echo "<img src='null.webp'>";
?>