<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;


class ArticleSeeder extends Seeder
{
    private function articles(): array
    {
        return [
            [
                'title' => 'Mengenal Thalassemia: Penyakit Darah yang Diturunkan',
                'content' => <<<'TEXT'
Thalassemia adalah kelainan darah genetik yang menyebabkan tubuh memproduksi hemoglobin secara tidak normal. Hemoglobin merupakan protein di dalam sel darah merah yang bertugas membawa oksigen ke seluruh tubuh. Ketika produksinya terganggu, sel darah merah menjadi cepat rusak sehingga penderitanya mengalami anemia.

Penyakit ini diturunkan dari orang tua kepada anak melalui gen. Artinya, Thalassemia tidak menular dan tidak dapat tertular melalui kontak fisik, melainkan diwariskan secara genetik. Itulah mengapa skrining pada calon orang tua menjadi langkah penting sebelum merencanakan kehamilan.

Secara umum Thalassemia terbagi menjadi Thalassemia Alfa dan Thalassemia Beta, tergantung rantai hemoglobin mana yang produksinya terganggu. Tingkat keparahannya pun beragam, mulai dari pembawa sifat tanpa gejala hingga bentuk berat yang memerlukan transfusi darah rutin.

Memahami Thalassemia sejak dini membantu keluarga mengambil keputusan yang lebih siap, termasuk melakukan pemeriksaan laboratorium dan berkonsultasi dengan dokter atau konselor genetik.
TEXT,
            ],
            [
                'title' => 'Carrier Thalassemia: Pembawa Sifat yang Sering Tidak Disadari',
                'content' => <<<'TEXT'
Seseorang disebut carrier atau pembawa sifat Thalassemia apabila ia membawa satu gen Thalassemia namun gen pasangannya normal. Pembawa sifat umumnya sehat dan tidak menunjukkan gejala berarti, sehingga banyak orang tidak menyadari statusnya hingga melakukan pemeriksaan darah.

Meski tampak sehat, pembawa sifat tetap dapat menurunkan gen Thalassemia kepada anaknya. Risiko terbesar muncul ketika dua orang pembawa sifat menikah: pada setiap kehamilan terdapat kemungkinan anak lahir dengan Thalassemia mayor, bentuk yang paling berat.

Karena itulah skrining sebelum menikah atau sebelum merencanakan kehamilan sangat dianjurkan. Pemeriksaan sederhana seperti hitung darah lengkap dan analisis hemoglobin dapat mengungkap status pembawa sifat.

Mengetahui status carrier bukan untuk menakut-nakuti, melainkan memberi keluarga informasi yang dibutuhkan untuk merencanakan masa depan dengan lebih bijak dan tenang.
TEXT,
            ],
            [
                'title' => 'Gejala dan Tanda Thalassemia yang Perlu Diwaspadai',
                'content' => <<<'TEXT'
Gejala Thalassemia sangat bergantung pada jenis dan tingkat keparahannya. Pada pembawa sifat, biasanya tidak ada gejala sama sekali. Namun pada Thalassemia bentuk sedang hingga berat, beberapa tanda dapat muncul sejak usia dini.

Tanda yang umum meliputi kulit pucat, mudah lelah dan lemas, sesak napas, serta pertumbuhan yang lebih lambat dibanding anak seusianya. Pada kasus berat, dapat terjadi pembesaran limpa, perubahan bentuk tulang wajah, dan urin berwarna lebih gelap.

Karena gejala anemia ini mirip dengan kondisi lain, diagnosis pasti hanya dapat ditegakkan melalui pemeriksaan laboratorium. Deteksi dini memungkinkan penanganan yang tepat sehingga kualitas hidup penderita dapat ditingkatkan.

Bila Anda atau anak Anda menunjukkan tanda anemia yang menetap, jangan ragu untuk memeriksakan diri ke fasilitas kesehatan untuk evaluasi lebih lanjut.
TEXT,
            ],
            [
                'title' => 'Golongan Darah dan Pewarisan Sifat dari Orang Tua',
                'content' => <<<'TEXT'
Golongan darah merupakan salah satu karakteristik fisik yang diturunkan secara genetik dari kedua orang tua. Sistem golongan darah ABO mengenal empat jenis utama: A, B, AB, dan O, yang ditentukan oleh kombinasi gen yang diwariskan ayah dan ibu.

Setiap orang tua menyumbang satu alel kepada anaknya. Kombinasi alel inilah yang menentukan golongan darah anak. Sebagai contoh, orang tua bergolongan A dan B dapat memiliki anak dengan golongan A, B, AB, maupun O, tergantung alel yang diturunkan.

Dalam konteks prediksi karakteristik bayi, golongan darah menjadi salah satu atribut fenotipe yang dianalisis. Pola pewarisan yang teratur membuat golongan darah relatif dapat diperkirakan berdasarkan data kedua orang tua.

Memahami pewarisan golongan darah tidak hanya menarik secara ilmiah, tetapi juga bermanfaat untuk keperluan medis seperti transfusi dan kehamilan.
TEXT,
            ],
            [
                'title' => 'Warna Iris, Tekstur Rambut, dan Bentuk Cuping: Fenotipe yang Diturunkan',
                'content' => <<<'TEXT'
Selain golongan darah, banyak ciri fisik atau fenotipe lain yang diwariskan dari orang tua kepada anak. Tiga di antaranya yang sering diamati adalah warna iris mata, tekstur rambut, dan bentuk cuping telinga.

Warna iris mata ditentukan oleh jumlah dan distribusi pigmen melanin. Warna yang lebih gelap umumnya bersifat dominan, sehingga sering muncul lebih sering pada keturunan, meski kombinasi gen kedua orang tua tetap menentukan hasil akhirnya.

Tekstur rambut, apakah lurus, bergelombang, atau keriting, juga dipengaruhi banyak gen. Begitu pula bentuk cuping telinga, yang umum dibedakan menjadi menempel atau terpisah (menggantung). Ciri-ciri ini menjadi contoh klasik dalam mempelajari pola pewarisan sifat.

Dalam sistem prediksi seperti GENETIKAKU, atribut fenotipe ini digunakan sebagai masukan untuk memperkirakan kemungkinan karakteristik fisik bayi. Hasilnya bersifat probabilistik, bukan kepastian, karena genetika manusia sangat kompleks.
TEXT,
            ],
            [
                'title' => 'Pentingnya Skrining Pranikah untuk Mencegah Thalassemia Mayor',
                'content' => <<<'TEXT'
Skrining pranikah adalah pemeriksaan kesehatan yang dilakukan sebelum pasangan memutuskan menikah, termasuk untuk mendeteksi status pembawa sifat Thalassemia. Langkah ini menjadi salah satu upaya pencegahan paling efektif terhadap kelahiran anak dengan Thalassemia mayor.

Ketika kedua calon pasangan diketahui sebagai pembawa sifat, mereka dapat memperoleh konseling genetik untuk memahami risiko dan pilihan yang tersedia. Informasi ini memberi ruang bagi pasangan untuk mengambil keputusan secara sadar dan bertanggung jawab.

Pemeriksaan yang dilakukan biasanya meliputi hitung darah lengkap dan analisis hemoglobin. Prosesnya sederhana, cepat, dan tersedia di banyak fasilitas kesehatan.

GENETIKAKU hadir sebagai alat skrining dan edukasi awal yang membantu calon orang tua mengenali risiko sejak dini. Namun perlu ditegaskan, hasil sistem ini tidak menggantikan diagnosis medis. Konsultasi dengan tenaga kesehatan tetap menjadi langkah yang tidak tergantikan.
TEXT,
            ],
        ];
    }

    public function run(): void
    {
        foreach ($this->articles() as $article) {
            Article::query()->updateOrCreate(
                ['slug' => Str::slug($article['title'])],
                [
                    'title' => $article['title'],
                    'content' => $article['content'],
                    'status' => 'published',
                ],
            );
        }
    }
}
