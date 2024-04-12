<?php

// Inisialisasi objek PDO untuk koneksi ke database
$kon = new PDO("mysql:host=localhost;dbname=db_perpustakaan", "root", "");

// Kelas ReviewHandler untuk menangani ulasan
class ReviewHandler
{
    private $kon; // Properti untuk menyimpan objek PDO

    // Konstruktor untuk inisialisasi objek PDO
    public function __construct(PDO $kon)
    {
        $this->kon = $kon;
    }

    // Metode untuk mengeksekusi query
    public function executeQuery($query, $data = [])
    {
        $statement = $this->kon->prepare($query);
        $statement->execute($data);

        return $statement;
    }

    // Metode untuk mengirim ulasan
    public function submitReview($idPustaka, $userName, $ratingData, $userReview)
    {
        $data = array(
            ':id_pustaka' => $idPustaka,
            ':user_name' => $userName,
            ':user_rating' => $ratingData,
            ':user_review' => $userReview,
            ':datetime' => time()
        );

        $query = "
            INSERT INTO review_table 
            (id_pustaka, user_name, user_rating, user_review, datetime) 
            VALUES (:id_pustaka, :user_name, :user_rating, :user_review, :datetime)
        ";

        $this->executeQuery($query, $data);

        echo "Ulasan & Rating Anda Berhasil Dikirim";
    }

    // Metode untuk mendapatkan ulasan
    public function getReviews($idPustaka)
    {
        $query = "
            SELECT * FROM review_table 
            WHERE id_pustaka = :id_pustaka
            ORDER BY review_id DESC
        ";

        $statement = $this->executeQuery($query, [':id_pustaka' => $idPustaka]);

        // Agregasi data ulasan
        $review_content = array();
        $total_review = 0;
        $total_user_rating = 0;
        $five_star_review = 0;
        $four_star_review = 0;
        $three_star_review = 0;
        $two_star_review = 0;
        $one_star_review = 0;

        foreach ($statement as $row) {
            $review_content[] = array(
                'user_name' => $row["user_name"],
                'user_review' => $row["user_review"],
                'rating' => $row["user_rating"],
                'datetime' => date('l jS, F Y h:i:s A', $row["datetime"])
            );

            // Menghitung jumlah ulasan berdasarkan rating
            switch ($row["user_rating"]) {
                case '5':
                    $five_star_review++;
                    break;
                case '4':
                    $four_star_review++;
                    break;
                case '3':
                    $three_star_review++;
                    break;
                case '2':
                    $two_star_review++;
                    break;
                case '1':
                    $one_star_review++;
                    break;
            }

            $total_review++;
            $total_user_rating += $row["user_rating"];
        }

        // Menghitung rata-rata rating
        $average_rating = $total_review > 0 ? $total_user_rating / $total_review : 0;

        // Membuat array output untuk dikonversi menjadi JSON
        $output = array(
            'average_rating' => number_format($average_rating, 1),
            'total_review' => $total_review,
            'five_star_review' => $five_star_review,
            'four_star_review' => $four_star_review,
            'three_star_review' => $three_star_review,
            'two_star_review' => $two_star_review,
            'one_star_review' => $one_star_review,
            'review_data' => $review_content
        );

        // Mengeluarkan data sebagai JSON
        echo json_encode($output);
    }
}

// Penggunaan contoh:
$reviewHandler = new ReviewHandler($kon);

// Pengecekan dan pemanggilan metode berdasarkan data POST
if (isset($_POST["rating_data"]) && isset($_POST["id_pustaka"])) {
    $reviewHandler->submitReview($_POST["id_pustaka"], $_POST["user_name"], $_POST["rating_data"], $_POST["user_review"]);
}

if (isset($_POST["action"]) && isset($_POST["id_pustaka"])) {
    $reviewHandler->getReviews($_POST["id_pustaka"]);
}
?>
