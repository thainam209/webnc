<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php require('inc/links.php'); ?>
  <title><?php echo "TELOT HOTEL" ?> - PHÒNG ĐẶT</title>
</head>

<body class="bg-light">

  <?php
  require('inc/header.php');

  if (!(isset($_SESSION['login']) && $_SESSION['login'] == true)) {
    redirect('index.php');
  }
  ?>


  <div class="container">
    <div class="row">

      <div class="col-12 my-5 px-4">
        <h2 class="fw-bold">ĐẶT PHÒNG</h2>
        <div style="font-size: 14px;">
          <a href="index.php" class="text-secondary text-decoration-none">TRANG CHỦ</a>
          <span class="text-secondary"> > </span>
          <a href="#" class="text-secondary text-decoration-none">PHÒNG ĐẶT</a>
        </div>
      </div>

      <?php


      $query = "SELECT bo.*, bd.* FROM `booking_order` bo
          INNER JOIN `booking_details` bd ON bo.booking_id = bd.booking_id
          WHERE  (bo.user_id=?)
          ORDER BY bo.booking_id DESC";

      $result = select($query, [$_SESSION['uId']], 'i');

      while ($data = mysqli_fetch_assoc($result)) {
        $date = date("d-m-Y", strtotime($data['datentime']));
        $checkin = date("d-m-Y", strtotime($data['check_in']));
        $checkout = date("d-m-Y", strtotime($data['check_out']));

        $status_bg = "";
        $btn = "";

        if ($data['booking_status'] == 'Đã Thanh Toán') {
          $status_bg = "bg-success";
          if ($data['arrival'] == 1) {
            // $btn = "<a href='generate_pdf.php?gen_pdf&id=$data[booking_id]' class='btn btn-dark btn-sm shadow-none'>Tải Xuống PDF</a>";

            if ($data['rate_review'] == 0) {
              $btn .= "<button type='button' onclick='review_room($data[booking_id],$data[room_id])' data-bs-toggle='modal' data-bs-target='#reviewModal' class='btn btn-dark btn-sm shadow-none ms-2'>Đánh Giá</button>";
            }
          } else {
            $btn = "<button onclick='cancel_booking($data[booking_id])' type='button' class='btn btn-danger btn-sm shadow-none'>Cancel</button>";
          }
        } else if ($data['booking_status'] == 'Đã Huỷ') {
          $status_bg = "bg-danger";

          if ($data['refund'] == 0) {
            $btn = "<span class='badge bg-primary'></span>";
          } else {
            // $btn = "<a href='generate_pdf.php?gen_pdf&id=$data[booking_id]' class='btn btn-dark btn-sm shadow-none'>Tải Xuống PDF</a>";
          }
        } else if ($data['booking_status'] == 'Đã Xác Nhận Đặt Phòng') {
          $status_bg = "bg-primary";

          if ($data['refund'] == 0) {
            $btn = "<span class='badge bg-primary'></span>";
          } else {
            // $btn = "<a href='generate_pdf.php?gen_pdf&id=$data[booking_id]' class='btn btn-dark btn-sm shadow-none'>Tải Xuống PDF</a>";
          }
        } else {
          $status_bg = "bg-warning";
          // $btn = "<a href='' onclick='' class='btn btn-danger btn-sm shadow-none'>Huỷ Đặt Phòng</a>";
          $btn = "<button onclick='cancel_booking($data[booking_id])' type='button' class='btn btn-danger btn-sm shadow-none'>Huỷ Đặt Phòng</button>";
          $btn .= "<button onclick='showQrModal($data[booking_id])' type='button' class='btn btn-success btn-sm shadow-none ms-2'>Thanh Toán</button>";
        }


        echo <<<bookings
            <div class='col-md-4 px-4 mb-4'>
              <div class='bg-white p-3 rounded shadow-sm'>
                <h5 class='fw-bold'>$data[room_name]</h5>
                <p>$data[price] vnđ</p>
                <p>
                  <b>Ngày Vào: </b> $checkin <br>
                  <b>Ngày Trả: </b> $checkout
                </p>
                <p>
                  <b>Tổng: </b> $data[total_pay] vnđ <br>
                  <b>ID Dơn: </b> $data[order_id] <br>
                  <b>Ngày Đặt: </b> $date
                </p>
                <p>
                  <span class='badge $status_bg'>$data[booking_status]</span>
                </p>
                $btn
              </div>
            </div>
          bookings;
      }

      ?>


    </div>
  </div>


  <div class="modal fade" id="reviewModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form id="review-form">
          <div class="modal-header">
            <h5 class="modal-title d-flex align-items-center">
              <i class="bi bi-chat-square-heart-fill fs-3 me-2"></i> Đánh Giá
            </h5>
            <button type="reset" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Đánh Giá</label>
              <select class="form-select shadow-none" name="rating">
                <option value="5">Rất Tốt</option>
                <option value="4">Tốt</option>
                <option value="3">Tạm</option>
                <option value="2">Kém</option>
                <option value="1">Rất Tệ</option>
              </select>
            </div>
            <div class="mb-4">
              <label class="form-label">Nhận Xét</label>
              <textarea type="password" name="review" rows="3" required class="form-control shadow-none"></textarea>
            </div>

            <input type="hidden" name="booking_id">
            <input type="hidden" name="room_id">

            <div class="text-end">
              <button type="submit" class="btn custom-bg text-white shadow-none">GỬI</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>



  <?php
  if (isset($_GET['cancel_status'])) {
    alert('success', 'Đặt phòng đã bị hủy!');
  } else if (isset($_GET['review_status'])) {
    alert('success', 'Cảm ơn bạn đã đánh giá!');
  }
  ?>

  <?php require('inc/footer.php'); ?>

  <script>
    function cancel_booking(id) {
      if (confirm('Bạn có chắc chắn hủy đặt phòng không?')) {
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "ajax/cancel_booking.php", true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        xhr.onload = function() {
          if (this.responseText == 1) {
            window.location.href = "bookings.php?cancel_status=true";
          } else {
            alert('error', 'Hủy không thành công!');
          }
        }

        xhr.send('cancel_booking&id=' + id);
      }
    }

    let review_form = document.getElementById('review-form');

    function review_room(bid, rid) {
      review_form.elements['booking_id'].value = bid;
      review_form.elements['room_id'].value = rid;
    }

    review_form.addEventListener('submit', function(e) {
      e.preventDefault();

      let data = new FormData();

      data.append('review_form', '');
      data.append('rating', review_form.elements['rating'].value);
      data.append('review', review_form.elements['review'].value);
      data.append('booking_id', review_form.elements['booking_id'].value);
      data.append('room_id', review_form.elements['room_id'].value);

      let xhr = new XMLHttpRequest();
      xhr.open("POST", "ajax/review_room.php", true);

      xhr.onload = function() {

        if (this.responseText == 1) {
          window.location.href = 'bookings.php?review_status=true';
        } else {
          var myModal = document.getElementById('reviewModal');
          var modal = bootstrap.Modal.getInstance(myModal);
          modal.hide();

          alert('error', "Xếp hạng & Đánh giá Không thành công!");
        }
      }

      xhr.send(data);
    })
    function showQrModal(bookingId) {
      // Cập nhật hình ảnh mã QR
      let qrCodeImage = document.getElementById('qrCodeImage');
      qrCodeImage.src = `https://scontent.fhan15-1.fna.fbcdn.net/v/t1.15752-9/494573332_1234921701629273_8744393724675142077_n.jpg?stp=dst-jpg_p480x480_tt6&_nc_cat=108&ccb=1-7&_nc_sid=0024fc&_nc_eui2=AeEayEkh-2tP8-ywqL0auWrNEfi3eKVjZboR-Ld4pWNlul0W5NHlA1oRilPDoE_i4895JsB7F-DaTnJlEfDBB_bY&_nc_ohc=J5ruBSjnkj0Q7kNvwFew2WY&_nc_oc=AdnSmnogvLvGYN2dcybXwyAIKRK7KA9tKM0RtUbuFD-O9JkWKRVlZgcf2-wSchbIIYE&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent.fhan15-1.fna&oh=03_Q7cD2QFWiZwSfgoivYqXVaCOF6LvOp4YAlMSp-6jks2HO1ESfQ&oe=684DACDA`;

      // Gắn bookingId vào modal
      let qrModal = document.getElementById('qrModal');
      qrModal.setAttribute('data-booking-id', bookingId);

      // Hiển thị modal
      let modalInstance = new bootstrap.Modal(qrModal);
      modalInstance.show();
    }
    function removePayButton() {
      // Lấy modal hiện tại
      let qrModal = document.getElementById('qrModal');
      let bookingId = qrModal.getAttribute('data-booking-id');

      // Tìm nút "Thanh Toán" bên ngoài và xóa nó
      let payButton = document.querySelector(`button[onclick='showQrModal(${bookingId})']`);
      if (payButton) {
        payButton.remove();
      }

      // Đóng modal
      let modalInstance = bootstrap.Modal.getInstance(qrModal);
      modalInstance.hide();
    }
  </script>

  <div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="qrModalLabel">Quét Mã QR</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body text-center">
          <img id="qrCodeImage" src="" alt="QR Code" class="img-fluid">
          <p class="mt-3">Quét mã QR để thanh toán.</p>
          <p class="mt-3">Khi đã chuyển tiền hãy ấn nút đã thanh toán.</p>
          <div>
            <span style="color: red; font-weight: bold;">
              Lưu ý:
            </span>
            <P>
            - Nếu chưa thanh toán mà ấn "đã thanh toán" thì phòng có thể bị hủy.
            </P>
            <P>
            - Khi chuyển khoản thành công hãy đợi máy chủ phản hồi.
            </P>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
          <button type="button" class="btn btn-success" id="markAsPaid" onclick="removePayButton()">Đã Thanh Toán</button>
        </div>
      </div>
    </div>
  </div>
</body>

</html>