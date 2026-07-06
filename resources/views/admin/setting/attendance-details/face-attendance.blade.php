<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>FixHR - Face Attendance</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100 p-6">
  <div class="max-w-7xl mx-auto">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Left Section -->
      <div class="flex flex-col items-center gap-4">
        <!-- Face Detection Box -->
        <div id="face-box" class="border-8 border-blue-500 rounded-xl overflow-hidden w-full h-[360px] flex items-center justify-center relative">
          <video id="video" autoplay playsinline class="object-cover w-full h-full"></video>
          <p id="faceMessage" class="absolute top-2 left-2 text-blue-500 text-xs font-semibold bg-white px-2 py-1 rounded">Initializing...</p>

          <!-- Shared name display -->
          <div class="absolute top-4 right-4 flex items-center gap-2 bg-white px-3 py-2 rounded shadow hidden" id="face-status">
            <span id="show-name" class="text-1xl font-semibold text-gray-800"></span>
            <span id="rightIcon" class="text-green-500 text-2xl hidden">✅</span>
            <span id="wrongIcon" class="text-red-500 text-2xl hidden">❌</span>
          </div>
          <!-- Face box loader overlay -->
          <div id="faceBoxLoader" class="absolute inset-0 bg-white bg-opacity-40 flex items-center justify-center z-40 hidden">
            <div class="text-center">
              <svg class="animate-spin h-8 w-8 text-blue-600 mx-auto mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-75" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-55" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
              </svg>
              <p class="text-blue-700 text-sm font-medium">Processing...</p>
            </div>
          </div>
        </div>

        <!-- Dynamic Welcome Message -->
        <div class="bg-white rounded-xl shadow-lg p-10 w-full text-center">
          <h2 class="text-lg font-bold text-blue-600 mt-2 leading-tight">Welcome to<br />{{$user->fh_business->b_name}}</h2>
          <p class="text-blue-600 mt-2 text-sm">
            {{$user->fh_business->b_tag_line}}
          </p>
        </div>
      </div>

      <!-- Right Section: Recent Check-in/out -->
      <div class="bg-white shadow-lg rounded-xl p-4 overflow-x-auto w-full mx-auto">
        <!-- DateTime Centered on Top -->
        <div class="flex justify-center mb-4">
          <div class="bg-blue-100 text-blue-800 rounded-lg px-6 py-1 text-center shadow">
            <h2 id="dateDisplay" class="text-xl font-bold mt-1"></h2>
          </div>
        </div>

        <!-- Header with Attendance Summary Inline -->
        <div class="flex justify-center gap-4 mb-4">
          <h3 class="text-gray-800 font-bold text-lg">Recent Activities <span class="text-xl font-bold">{{ $totalToDayAttendance }}/{{ $activeEmployeeCount }}</span></h3>
        </div>

        <!-- Attendance Table -->
        <table class="min-w-full text-sm text-left border border-blue-300">
          <thead>
            <tr class="bg-blue-100 text-blue-700 whitespace-nowrap">
              <th class="px-4 py-2 border">S. No</th>
              <th class="px-4 py-2 border">Emp ID</th>
              <th class="px-4 py-2 border">Emp Name</th>
              <th class="px-4 py-2 border">Check-in</th>
              <th class="px-4 py-2 border">Check-out</th>
            </tr>
          </thead>
          <tbody id="checkInTableBody">
            @if(count($attendanceData))
              @foreach($attendanceData as $index => $entry)
                <tr class="hover:bg-gray-50 whitespace-nowrap">
                  <td class="px-4 py-2 border">{{ $index + 1 }}</td>
                  <td class="px-4 py-2 border">{{ $entry->fh_employee->emp_code }}</td>
                  <td class="px-4 py-2 border">{{ $entry->fh_employee->emp_full_name }}</td>
                  <td class="px-4 py-2 border">{{ $entry->atd_check_in_time ? \Carbon\Carbon::parse($entry->atd_check_in_time)->format('h:i A') : '-' }}</td>
                  <td class="px-4 py-2 border">{{ $entry->atd_check_out_time ? \Carbon\Carbon::parse($entry->atd_check_out_time)->format('h:i A') : '-' }}</td>
                </tr>
              @endforeach
            @else
              <tr>
                <td colspan="5" class="px-4 py-2 border text-center">No recent records.</td>
              </tr>
            @endif
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
    const updateClock = () => {
      const now = new Date();
      const options = {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: true
      };
      const formatted = now.toLocaleString('default', options);
      document.getElementById("dateDisplay").textContent = formatted;
    };
    
    updateClock();
    setInterval(updateClock, 1000);
    
    const captureState = {
      // lastSuccessTime: 0,
      lastSuccessTime: Date.now()
      capturing: false,
      intervalId: null,
    };
    
    const elements = {
      faceMessage: document.getElementById('faceMessage'),
      video: document.getElementById("video"),
      faceBox: document.getElementById("face-box"),
      rightIcon: document.getElementById("rightIcon"),
      wrongIcon: document.getElementById("wrongIcon"),
      showName: document.getElementById("show-name"),
      faceStatus: document.getElementById("face-status"),
      faceBoxLoader: document.getElementById("faceBoxLoader")
    };
    
    let speaking = false;
    
    const speak = (text) => {
      if (speaking) return;
    
      speaking = true;
      speechSynthesis.cancel(); // clear queue if any
    
      const utterance = new SpeechSynthesisUtterance(text);
      utterance.lang = 'en-US';
      utterance.pitch = 1;
      utterance.rate = 1;
    
      utterance.onend = () => {
        speaking = false;
      };
    
      utterance.onerror = () => {
        speaking = false;
      };
    
      speechSynthesis.speak(utterance);
    };
    
    const canvas = document.createElement("canvas");
    const ctx = canvas.getContext("2d");
    
    const initializeCamera = async () => {
      try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: true });
        elements.video.srcObject = stream;
        await elements.video.play();
        handleCameraSuccess();
      } catch (error) {
        console.error("Camera access error:", error);
      }
    };
    
    const handleCameraSuccess = () => {
      elements.faceMessage.textContent = "Camera active. Awaiting face...";
      startCapture();
    };
    
    const startCapture = () => {
      if (!captureState.capturing) {
        captureState.capturing = true;
        captureState.intervalId = setInterval(processFrame, 500);
      }
    };
    
    const stopCapture = () => {
      captureState.capturing = false;
      clearInterval(captureState.intervalId);
    
      // Reset UI while waiting
      elements.faceMessage.textContent = "Processing...";
      elements.faceMessage.classList.remove("text-green-500");
      elements.faceMessage.classList.add("text-blue-500");
    };
    
    const processFrame = async () => {
      if (!captureState.capturing) return;
       
      canvas.width = elements.video.videoWidth;
      canvas.height = elements.video.videoHeight;
      ctx.drawImage(elements.video, 0, 0, canvas.width, canvas.height);
          
      try {
          const fastApiBaseUrl = "{{ app('App\\Helpers\\ApiHelper')::FASTAPI_BASE_URL }}";
          const response = await fetch(fastApiBaseUrl+'/detect', {

          method: "POST",
          headers: {
            "Content-Type": "application/json",
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          body: JSON.stringify({ image: canvas.toDataURL("image/jpeg").split(",")[1] }),
        });
    
        const result = await response.json();
        if (result?.status) {
          const imageBase64 = result.face_image;
          captureState.lastSuccessTime = Date.now();
    
          elements.faceMessage.textContent = "Face Detected..";
          elements.faceMessage.classList.add("text-green-500");
          elements.faceMessage.classList.remove("text-blue-500");
          elements.faceBox.classList.add("border-green-500");
          elements.faceBox.classList.remove("border-blue-500");
    
          stopCapture(); // stop immediately
          uploadImage(imageBase64); // resume only after this
        }
      } catch (err) {
        console.error("Detection error:", err);
        elements.faceMessage.textContent = "Unexpected server issue. Please contact support for help";
      }
    };
    
    let lastFrameTime = Date.now();

    const uploadImage = (faceBase64) => {
      elements.faceBoxLoader.classList.remove("hidden");

      // ✅ 5 minute old frame ko ignore karo
      const now = Date.now();
      if ((now - lastFrameTime) > 5 * 60 * 1000) {
        console.log("Skipping old frame (older than 5 minutes)");
        elements.faceBoxLoader.classList.add("hidden");
        resetUI();
        startCapture();
        return;
      }

      // Convert Base64 to Blob
      const byteCharacters = atob(faceBase64);
      const byteNumbers = new Array(byteCharacters.length).fill(0).map((_, i) => byteCharacters.charCodeAt(i));
      const byteArray = new Uint8Array(byteNumbers);
      const blob = new Blob([byteArray], { type: "image/jpeg" });

      const formData = new FormData();
      formData.append('image', blob, `${generateUUID()}.jpg`);

      $.ajax({
        url: "{{ route('upload.visitor.face') }}",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
      }).done((response) => {
        elements.faceBoxLoader.classList.add("hidden");
        elements.faceStatus.classList.remove("hidden");

        if (response.status) {
          elements.rightIcon.classList.remove("hidden");
          elements.wrongIcon.classList.add("hidden");
          elements.showName.textContent = response.result.name;
          elements.showName.classList.remove("text-red-500");
          elements.showName.classList.add("text-green-500");
          updateCheckInTable(response.result.recent_attendance || []);
          // speak('Hi ' + response.result.name + ' ' + response.result.attendance_message);
          speak('Hi ' + response.result.name);
        } else {
          speak(response.message);
          elements.rightIcon.classList.add("hidden");
          elements.wrongIcon.classList.remove("hidden");
          elements.showName.textContent = response.message;
          elements.showName.classList.remove("text-green-500");
          elements.showName.classList.add("text-red-500");
          elements.faceBox.classList.remove("border-green-500");
          elements.faceBox.classList.add("border-red-500");
        }

        resetUI();
        setTimeout(() => {
          startCapture();
        }, 1000);

      }).fail((err) => {
        console.error("Upload failed:", err);
        speak("Upload failed. Please try again.");
        elements.faceBoxLoader.classList.add("hidden");
        resetUI();
        startCapture();
      });
    };

    
    const resetUI = () => {
      elements.faceMessage.textContent = "Camera active. Awaiting face...";
      elements.faceMessage.classList.remove("text-green-500", "text-red-500");
      elements.faceMessage.classList.add("text-blue-500");
      elements.faceBox.classList.remove("border-green-500", "border-red-500");
      elements.faceBox.classList.add("border-blue-500");
    //   elements.rightIcon.classList.add("hidden");
    //   elements.wrongIcon.classList.add("hidden");
    //   elements.showName.textContent = "";
    //   elements.faceStatus.classList.add("hidden");
    };

    // ✅ Clear captured frame every 5 minutes
    setInterval(() => {
      console.log("Clearing stale frame after 5 minutes...");
      lastFrameTime = Date.now(); // reset reference
      resetUI();
    }, 5 * 60 * 1000);
    
    const formatDateTime = (isoString) => {
      if (!isoString) return '-';
      const date = new Date(isoString);
      let hours = date.getHours();
      const minutes = date.getMinutes().toString().padStart(2, '0');
      const ampm = hours >= 12 ? 'PM' : 'AM';
      hours = hours % 12 || 12;
      return `${hours}:${minutes} ${ampm}`;
    };
    
    const updateCheckInTable = (checkIns) => {
      const tbody = document.getElementById("checkInTableBody");
      tbody.innerHTML = "";
      if (!checkIns.length) {
        tbody.innerHTML = `<tr><td colspan="5" class="px-4 py-2 border text-center">No recent records.</td></tr>`;
        return;
      }
      checkIns.forEach((entry, index) => {
        const row = `
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-2 border">${index + 1}</td>
            <td class="px-4 py-2 border">${entry.fh_employee.emp_code}</td>
            <td class="px-4 py-2 border">${entry.fh_employee.emp_full_name}</td>
            <td class="px-4 py-2 border">${formatDateTime(entry.atd_check_in_time)}</td>
            <td class="px-4 py-2 border">${formatDateTime(entry.atd_check_out_time)}</td>
          </tr>`;
        tbody.innerHTML += row;
      });
    };
    
    const generateUUID = () => Date.now().toString(36) + Math.random().toString(36).substr(2, 9);
    
    // Start everything
    initializeCamera();

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                fetch('/store-location-by-web', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude
                    })
                })
                .then(response => {
                    if (!response.ok) console.error('Failed to store location');
                })
                .catch(error => console.error('Network error storing location:', error));
            },
            function(error) {
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        alert("Location access denied. Please enable location then refresh.");
                        break;
                    case error.POSITION_UNAVAILABLE:
                        console.error("Location services unavailable or disabled.");
                        const message = "Location services are disabled.\n\n" +
                                        "Option 1: Enable location in device settings.\n" +
                                        "- Android: Settings > Location > Turn On\n" +
                                        "- iOS: Settings > Privacy > Location Services > Turn On\n" +
                                        "Option 2: Click OK to continue without location.";
                        if (confirm(message)) {
                            console.log("User chose to continue without location.");
                        } else {
                            console.log("User may enable location.");
                            setTimeout(() => {
                                if (confirm("Try accessing location again?")) {
                                    window.location.reload();
                                }
                            }, 1000);
                        }
                        break;
                    case error.TIMEOUT:
                        console.error("Location request timed out.");
                        alert("Location request timed out.");
                        break;
                    default:
                        console.error("Unknown geolocation error.");
                        alert("Error accessing location.");
                        break;
                }
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 300000
            }
        );
    } else {
        alert("Geolocation not supported by this browser.");
    }
  </script>
</body>
</html>
