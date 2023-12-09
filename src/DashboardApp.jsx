import React from "react";
import SparkLine from "react-canvas-spark-line";

// date is like '2020-12-25'
function getDaysFromStart(testDate, date) {
  const orderDate = new Date(date);
  const timeDifference = orderDate.getTime() - testDate.getTime();
  return Math.ceil(timeDifference / (1000 * 60 * 60 * 24));
}

export default function DashboardApp() {
  const [customers, setCustomers] = React.useState(null);

  let testDate = new Date();
  testDate.setDate(testDate.getDate() - 30);

  React.useEffect(() => {
    document.title = "Dashboard";

    async function populateData() {
      let response = await fetch("api/orders.php");
      response = await response.json();

      setCustomers(response);
    }

    populateData();
  }, []);

  if (!customers) {
    return "Loading...";
  }

  return (
    <table cellpadding="5">
      <tr>
        <th></th>
        <th>User</th>
        <th>Fees</th>
        <th>Stripe linked</th>
        <th>#Services</th>
        <th>#Trucks</th>
        <th>#Bins</th>
        <th>#Staff</th>
        <th>Sales last 30 days</th>
      </tr>
      {customers.map((customer) => {
        const subdomain = customer.domain.replace(".binbooker.com", "");

        const ordersByDate = Array(30).fill(0); // Make this: [0,0,0,3,0,1,1,2,0,7,...]
        customer.orders.forEach((date) => {
          const dateIndex = getDaysFromStart(testDate, date.date);
          ordersByDate[dateIndex] = date.count;
        });

        return (
          <tr>
            <td valign="top">{customer.id}</td>
            <td>
              {subdomain}
              <br />
              <a href={`${customer.website}`} target="_blank">
                Website
              </a>
              &nbsp;
              <a href={`https://${subdomain}.binbooker.com`} target="_blank">
                Front
              </a>
              &nbsp;
              <a href={`https://${subdomain}.binbooker.com/back`} target="_blank">
                Back
              </a>
              &nbsp;
              <a href={`http://binbooker.test/sales-crm/?s=${subdomain}`} target="_blank">
                CRM
              </a>
              &nbsp;
              <br />
              {customer.name}
              <br />
              {`${customer.city} ${customer.province}`}
              <br />
              {customer.phone}
              <br />
              {customer.email}
            </td>
            <td>${`${customer.feePerOrderFE}/$${customer.maxFeePerMonth}`}</td>
            <td>{customer.stripeLinked ? "." : "NO!!"}</td>
            <td>{customer.numServices}</td>
            <td>{customer.numTrucks}</td>
            <td>{customer.numBins}</td>
            <td>{customer.numStaff}</td>
            <td>
              <SparkLine
                width={500}
                height={Math.max(...ordersByDate) * 10}
                color="#138B8B"
                data={ordersByDate}
                areaOpacity={1}
                areaColor="#cde8e8"
              />
            </td>
          </tr>
        );
      })}
    </table>
  );
}
