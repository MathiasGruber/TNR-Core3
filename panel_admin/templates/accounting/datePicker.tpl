{literal}
<script>
  $(function() {
    $( "#startPick" ).datepicker();
  });
  $(function() {
    $( "#endPick" ).datepicker();
  });
</script>
{/literal}

<div align="center">
    <table width="95%" class="table">
        <tr>
          <td class="subHeader">Accounting</td>
        </tr>
        <tr>
          <td>
            <form id="accountingForm" name="accountingForm" method="post" action="">
            <table border="0" style="border-collapse: collapse; border: 0px solid #000000;">
                <tr>
                    <td>
                        Start Date: <br>
                        <input type="text" name="startDate" id="startPick" />
                    </td>
                    <td style="padding-left:10px;">
                        End Date: <br>
                        <input type="text" name="endDate" id="endPick" />
                    </td>
                    <td style="padding-left:10px;">
                        <br>
                        <input type="submit"  style="height: 25px; width: 150px;" class="button" name="Submit" value="Create Report" />
                    </td>
                </tr>
            </table>  
            </form>     
          </td>
        </tr>
    </table>
</div>