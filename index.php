<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['fileToUpload'])) {
    define('TH', intval($_POST['threshold'])); // Get the threshold value from the form

    // Process the uploaded file
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) {
      mkdir($target_dir, 0777, true);
    }
    $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is an actual image or fake image
    if(isset($_POST["submit"])) {
        $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
        if($check !== false) {
            $uploadOk = 1;
        } else {
            echo "File is not an image.";
            $uploadOk = 0;
        }
    }

    // Check if file already exists
    if (file_exists($target_file)) {
        echo "Sorry, file already exists.";
        $uploadOk = 0;
    }

    // Check file size
    if ($_FILES["fileToUpload"]["size"] > 5000000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
    && $imageFileType != "gif" ) {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    } else {
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            // Load the image
            $image = imagecreatefromstring(file_get_contents($target_file));

            // Get image dimensions
            $width = imagesx($image);
            $height = imagesy($image);

            // Create a new true color image with transparency
            $transparentImage = imagecreatetruecolor($width, $height);
            imagesavealpha($transparentImage, true);
            $transparency = imagecolorallocatealpha($transparentImage, 0, 0, 0, 127);
            imagefill($transparentImage, 0, 0, $transparency);

            // Iterate over each pixel to set transparency
            for ($x = 0; $x < $width; $x++) {
                for ($y = 0; $y < $height; $y++) {
                    // Get the current pixel's color
                    $rgb = imagecolorat($image, $x, $y);
                    $colors = imagecolorsforindex($image, $rgb);

                    // Check if the current pixel color is greater than the threshold
                    if ($colors['red'] > TH && $colors['green'] > TH && $colors['blue'] > TH) {
                        imagesetpixel($transparentImage, $x, $y, $transparency);
                    } else {
                        $color = imagecolorallocatealpha($transparentImage, $colors['red'], $colors['green'], $colors['blue'], 0);
                        imagesetpixel($transparentImage, $x, $y, $color);
                    }
                }
            }

            // Save the resulting image
            $output_path = 'processed_' . basename($target_file);
            imagepng($transparentImage, $output_path);

            // Clean up
            imagedestroy($image);
            imagedestroy($transparentImage);

            // Force download the processed image
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($output_path).'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($output_path));
            readfile($output_path);
            exit;

        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transparent Image Processor</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Transparent Image Processor</h2>
        <p>Upload an image file and adjust the slider to set the transparency threshold. The white background will be made transparent based on the threshold you set.</p>
        <form action="" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="fileToUpload">Select image to upload:</label>
                <input type="file" name="fileToUpload" id="fileToUpload" class="form-control-file">
            </div>
            <div class="form-group">
                <label for="threshold">Transparency Threshold (0-255):</label>
                <input type="range" class="custom-range" id="threshold" name="threshold" min="0" max="255" value="128">
                <span id="thresholdValue">128</span>
            </div>
            <button type="submit" name="submit" class="btn btn-primary">Upload Image</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script>
        // Update the current slider value (each time you drag the slider handle)
        $('#threshold').on('input', function() {
            $('#thresholdValue').text($(this).val());
        });
    </script>
</body>
</html>

