<!DOCTYPE html>
<html>
<head>
   <title>Receipt</title>
   <style>
      body{
         margin-top:20px;
         color: #2e323c;
         background: #f5f6fa;
         position: relative;
         height: 100%;
      }
     
      table , td , th , tr{
         width: 100%;
      }
      th{
         background: #467FCF;
         color: white;
         padding: 10px;

      }

      .footer{
         background: rgb(185, 182, 182);
         margin-top:70px;
      }
   </style>

</head>
<body>
<div>
   <table style="margin-bottom:20px;">
      {{-- <tr>
         <td colspan="8" >
            <p>Some Adress</p>
            <p>Another Address</p>
         </td>
      </tr> --}}
   </table>

   <table style="margin-bottom:50px;">
      <tr style="margin-top:50px;">
         <td colspan="12" style="color:green;"><h4>Parent Account : {{ $invoiceData['account']['data']['parent_account']['data']['display_name'] ?? null }} </h6></td>
      </tr>
   </table>
  <table>
   <tr rowspan="4">
      <th colspan="1">Entries</th>
      <th>Type</th>
      <th>Source</th>
      <th colspan="1">Memo</th>
      <th colspan="1">Currency</th>
      <th colspan="1">Amount</th>
   </tr>
   <tr>
      <td colspan="1">{{ $invoiceData['account']['data']['display_name'] }}</td>
      <td> {{ $invoiceData['entry_type'] }}</td>
      <td> {{ $invoiceData['source_info'] }} </td>
      <td colspan="1">{{ $invoiceData['memo'] }}</td>
      <td colspan="1">{{ $invoiceData['currency'] }}</td>
      <td colspan="1">{{ $invoiceData['amount']['formatted'] }}</td>
   </tr>
   <tr class="footer">
      <td colspan="5">
        <b>Sub Total</b>
      </td>
      <td colspan="1">
         <b>{{ $invoiceData['amount']['formatted'] }}</b>
       </td>
   </tr>
  </table>

</div>



</body>
</html>