<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Face Detection</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
</head>
<body class="bg-gray-100">
    <div class="flex items-start justify-center min-h-screen p-8">
        <div class="bg-white rounded-2xl shadow-lg p-6 w-full max-w-4xl grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="flex flex-col items-center sm:items-start sm:col-span-2">
                <div class="w-450 h-150 rounded-xl overflow-hidden">
                    <video id="webcam" autoplay playsinline class="webcam"></video>
                </div>
            </div>

            <div class="sm:col-span-1 flex flex-col justify-center">
                <h3 id="uploadResultMessage" class="text-lg font-semibold mb-2 text-red-500">
                    Please look at the camera
                </h3>

                <div id="employeeDetails" class="hidden">
                    <h2 id="employeeName" class="text-2xl font-bold text-gray-800"></h2>
                    <p id="employeeDesignation" class="text-sm text-gray-500 mb-4"></p>

                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Department</p>
                            <p id="employeeDepartment" class="text-base text-gray-900"></p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600">Email</p>
                            <p id="employeeEmail" class="text-base text-gray-900"></p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600">Phone</p>
                            <p id="employeePhone" class="text-base text-gray-900"></p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600">Location</p>
                            <p id="employeeAddress" class="text-base text-gray-900"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>

        let uploadResultMessage = document.getElementById('uploadResultMessage');
        let employeeDetails = document.getElementById('employeeDetails');
        let employeeName = document.getElementById('employeeName');
        let employeeDesignation = document.getElementById('employeeDesignation');
        let employeeDepartment = document.getElementById('employeeDepartment');
        let employeeEmail = document.getElementById('employeeEmail');
        let employeePhone = document.getElementById('employeePhone');
        let employeeAddress = document.getElementById('employeeAddress');

        async function loadModels() {
            await faceapi.tf.setBackend('webgl');
            await faceapi.tf.ready();

            try {
                await Promise.all([
                    faceapi.nets.tinyFaceDetector.loadFromUri('/face-api/models'),
                    faceapi.nets.faceLandmark68Net.loadFromUri('/face-api/models'),
                    faceapi.nets.faceExpressionNet.loadFromUri('/face-api/models'),
                    faceapi.nets.ssdMobilenetv1.loadFromUri('/face-api/models')
                ]);
                console.log('Face API models loaded successfully');
            } catch (error) {
                console.error('Failed to load models:', error);
                uploadResultMessage.textContent = 'Error loading face detection models.';
            }
        }

        async function captureAndSendImage() {
            const video = document.getElementById('webcam');
            if (!video || video.readyState !== 4) return;

            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            if (!ctx) return;

            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);



            canvas.toBlob(async (blob) => {
                if (!blob) return;

                const faceDetected = await detectFaceLocally(blob);
                if (!faceDetected) {
                    uploadResultMessage.textContent = 'No face detected. Please adjust your position.';
                    return;
                }

                let formData = new FormData();
                const visitorImageName = generateUUID();
                formData.append('image', blob, visitorImageName + '.jpg');

                try {
                    $.ajax({
                        url: "{{ route('upload.visitor.image') }}",
                        type: "POST",
                        data: formData,
                        processData: false, // Prevent jQuery from processing FormData
                        contentType: false, // Let the browser set the content type
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // Adding CSRF token
                        },
                        beforeSend: function() {
                            console.log("Uploading image...");
                        },
                        success: function(response) {
                            console.log("Success:", response);
                             if (response.status) {
                                // Update the employee details in the HTML
                                $("#employeeName").text(response.result.name);
                                $("#employeeDesignation").text(response.result.designation);
                                $("#employeeDepartment").text(response.result.department);
                                $("#employeeEmail").text(response.result.email);
                                $("#employeePhone").text(response.result.phone);
                                $("#employeeAddress").text(response.result.address);
                                $('#uploadResultMessage')
                                    .text(response.result.attendance_message)
                                    .removeClass('text-red-500') // Remove any error color if previously set
                                    .addClass('text-green-500'); // Add green color

                                // Show the employee details section
                                $("#employeeDetails").removeClass("hidden");
                            } else {
                                console.error("Error: Invalid response");
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("Error uploading image:", error);
                        }
                    });
                } catch (error) {
                    console.error("Error uploading image:", error);
                }
            }, 'image/jpeg');
        }

        function generateUUID() {
            return Date.now().toString(36) + Math.random().toString(36).substr(2, 9);
        }

        function speakMessage(message) {
            if ('speechSynthesis' in window) {
                const utterance = new SpeechSynthesisUtterance(message);
                utterance.lang = 'en-US';
                utterance.rate = 1;
                utterance.pitch = 1;
                speechSynthesis.speak(utterance);
            } else {
                console.warn('Web Speech API is not supported in this browser.');
            }
        }

        async function detectFaceLocally(imageBlob) {
            const image = await blobToImage(imageBlob);
            const detections = await faceapi.detectAllFaces(image, new faceapi.TinyFaceDetectorOptions({
                inputSize: 128,
                scoreThreshold: 0.5,
            }));

            return detections.length > 0;
        }

        function blobToImage(blob) {
            return new Promise((resolve, reject) => {
                const img = new Image();
                img.onload = () => resolve(img);
                img.onerror = reject;
                img.src = URL.createObjectURL(blob);
            });
        }

        async function startCapture() {
            await captureAndSendImage();
            setTimeout(startCapture, 10000);
        }

        document.addEventListener('DOMContentLoaded', async () => {
            await loadModels();
            const stream = await navigator.mediaDevices.getUserMedia({ video: true });
            const video = document.getElementById('webcam');
            video.srcObject = stream;
            startCapture();
        });
    </script>
</body>
</html>
