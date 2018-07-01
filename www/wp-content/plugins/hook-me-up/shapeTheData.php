<?php 
$result = $_SESSION['result'];
if($woocommerce) {
   // print_r( $lastRequest = $woocommerce->http->getBody());
    }
    ?>

  <main class="main-area">

    <nav class="task-list">
    <ul>
    <?php 
      foreach ($result as  $value ){
        $id =  $value['id'];
        $name =  $value['name'];
        $price =  $value['price'];
        
    ?>
    <li>
      <a href="single.php?id=<?php echo $id; ?>">
      <h2><?php echo $name ?></h2>
      <p><?php echo '£'.$price; ?></p>
      </a>
    </li>
 
    </li>
  <?php  } ?>
  
    </ul>
    </nav><!-- .task-list -->

  </main><!-- .main-area -->