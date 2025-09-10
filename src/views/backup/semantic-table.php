 <table class="table" summary="Sales by region for Q1 with totals">
     <!-- Visible caption for sighted users -->
     <caption class="table__caption">Q1 Sales — 2025</caption>

     <!-- Optional: group columns for styling -->
     <colgroup>
         <col class="table__col table__col--region">
         <col class="table__col table__col--q1">
         <col class="table__col table__col--q2">
         <col class="table__col table__col--total">
     </colgroup>

     <!-- Column headers -->
     <thead class="table__head">
         <tr>
             <th scope="col">Region</th>
             <th scope="col">January — Feb</th>
             <th scope="col">March</th>
             <th scope="col">Total</th>
         </tr>
     </thead>

     <!-- Screen-reader-only description (optional) -->
     <caption class="visually-hidden" id="table-desc">
         This table shows Q1 sales broken down by region. Numbers are in thousands of euros.
     </caption>

     <tbody class="table__body" aria-describedby="table-desc">
         <tr>
             <th scope="row">North</th>
             <td>120</td>
             <td>80</td>
             <td>200</td>
         </tr>

         <tr>
             <th scope="row">South</th>
             <td>95</td>
             <td>105</td>
             <td>200</td>
         </tr>

         <tr>
             <th scope="row">International</th>
             <td>150</td>
             <td>170</td>
             <td>320</td>
         </tr>
     </tbody>

     <!-- Footer for totals or notes -->
     <tfoot class="table__foot">
         <tr>
             <th scope="row">Total</th>
             <td colspan="2" aria-hidden="true"></td>
             <td>720</td>
         </tr>
     </tfoot>
 </table>