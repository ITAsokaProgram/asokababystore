document.getElementById("btn-bar").addEventListener("click", function (e) {
    e.preventDefault();
    isSubdeptActive = true;
    let formData = new FormData();
    formData.append("ajax", true);
    formData.append("kd_store", document.querySelector("#cabang").value);
    formData.append("start_date", document.querySelector("#date")?.value);
    formData.append("end_date", document.querySelector("#date1")?.value);
    formData.append("subdept", document.querySelector("#subdept").value);
    formData.append("kode_supp", document.querySelector("#kode_supp").value);
    formData.append("query_type", this.value);
  
    console.log("🔄 Mengirim data ke server:", Object.fromEntries(formData));
  
    $.ajax({
      url: "http://localhost/asoka-id/in_laporan_sub_dept.php?ajax=1",
      method: "POST",
      data: formData,
      processData: false,
      contentType: false,
      success: function (response) {
        console.log("✅ Response dari server (RAW):", response);
        let jsonResponse;
        try {
          jsonResponse =
            typeof response === "string" ? JSON.parse(response) : response;
          console.log("📋 Parsed JSON Response:", jsonResponse);
        } catch (error) {
          console.error("❌ Gagal parsing JSON:", error, response);
          return;
        }
  
        if (jsonResponse && jsonResponse.status === "success") {
          if (jsonResponse.tableData) {
            console.log("✅ Table Data Ditemukan:", jsonResponse.tableData);
          } else {
            console.warn(
              "⚠️ Table Data Tidak Ditemukan di Response:",
              jsonResponse
            );
          }
        } else {
          console.warn("⚠️ Server mengembalikan status error:", jsonResponse);
        }
        if (jsonResponse.data && jsonResponse.labels) {
          updateBarChart(jsonResponse.labels, jsonResponse.data, jsonResponse.tableData);
        }
      },
    });
  });

function updateBarChart(labels, data,table){
    console.log("📊 Updating Bar Chart with Data:", labels, data,table);
    
    var newData = labels.map((label, index) => ({
      promo: label,
      Qty: Number(data[index]),
      tanggal: String(table[index]?.periode)
    }));
    console.log("Data Tabel: ", newData);
    var promo = newData.map(item => item.promo);
    var tanggal = newData.map(item => item.tanggal); // Ambil tanggal
    var qty = newData.map(item => item.Qty); // Ambil qty dalam bentuk angka
    var app = {};
    var barChart = echarts.init(document.getElementById("barDiagram"));
    var optionBarCharts;
    const posList = [
        'left',
        'right',
        'top',
        'bottom',
        'inside',
        'insideTop',
        'insideLeft',
        'insideRight',
        'insideBottom',
        'insideTopLeft',
        'insideTopRight',
        'insideBottomLeft',
        'insideBottomRight'
    ];
    app.configParameters = {
        rotate: {
            min: -90,
            max: 90
        },
        align: {
            options: {
                left: 'left',
                center: 'center',
                right: 'right'
            }
        },
        verticalAlign: {
            options: {
                top: 'top',
                middle: 'middle',
                bottom: 'bottom'
            }
        },
        position: {
            options: posList.reduce(function (map, pos) {
                map[pos] = pos;
                return map;
            }, {})
        },
        distance: {
            min: 0,
            max: 100
        }
    };
    app.config = {
        rotate: 52,
        align: 'left',
        verticalAlign: 'middle',
        position: 'top',
        distance: 15,
        onChange: function () {
            const labelOption = {
                rotate: app.config.rotate,
                align: app.config.align,
                verticalAlign: app.config.verticalAlign,
                position: app.config.position,
                distance: app.config.distance
            };
            barChart.setOption({
                series: [
                    {
                        label: labelOption
                    },
                    {
                        label: labelOption
                    },
                    {
                        label: labelOption
                    },
                    {
                        label: labelOption
                    }
                ]
            });
        }
    };
    const labelOption = {
        show: true,
        position: app.config.position,
        distance: app.config.distance,
        align: app.config.align,
        verticalAlign: app.config.verticalAlign,
        rotate: app.config.rotate,
        formatter: '{c}  {name|{a}}',
        fontSize: 16,
        rich: {
            name: {}
        }
    };
    optionBarCharts = {
        tooltip: {
            trigger: 'axis',
            axisPointer: {
                type: 'shadow'
            }
        },
        toolbox: {
            show: true,
            orient: 'vertical',
            left: 'right',
            top: 'center',
            feature: {
                mark: { show: true },
                dataView: { show: true, readOnly: false },
                magicType: { show: true, type: ['line', 'bar', 'stack'] },
                restore: { show: true },
                saveAsImage: { show: true }
            }
        },
        xAxis: [
            {
                type: 'category',
                axisTick: { show: false },
                data: tanggal
            }
        ],
        yAxis: [
            {
                type: 'value'
            }
        ],
        series: [
            {
                name: 'Forest',
                type: 'bar',
                barGap: 0,
                label: labelOption,
                emphasis: {
                    focus: 'series'
                },
                data: qty
            },
        ]
    };
  
    barChart.setOption(optionBarCharts);
    document.getElementById("chartDiagram").style.display = "none";
    // END CODE Bar Echart JS 
  }