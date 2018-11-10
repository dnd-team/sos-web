
            var url = "";

            var doc = new jsPDF('l','pt');

            var columns = [
                {title: "", dataKey: "block"},
                {title: "", dataKey: "time"},
                {title: "5/6", dataKey: "grade-5"},
                {title: "7", dataKey: "grade-7"},
                {title: "8", dataKey: "grade-8"},
                {title: "9", dataKey: "grade-9"},
                {title: "10", dataKey: "grade-10"},
                {title: "", dataKey: "block"},
                {title: "", dataKey: "time"},
                {title: "Q1", dataKey: "grade-11"},
                {title: "Q2", dataKey: "grade-12"},
                {title: "Lehrer", dataKey: "teacher"},
            ];
            var rows = [
                {"block": "1. Block", "time": "8 - 9 Uhr","grade-5":"5a Deu MJ Ausfall","grade-7":"7a Deu MJ Ausfall","grade-8":"8a Deu MJ Ausfall","grade-9":'9a Deu MJ Ausfall,Auftrag für zuhause',"grade-10":"10b Deu MJ Ausfall","block":"1. Block","time":"8 - 9 Uhr","grade-11":"11 Deu MJ Ausfall","grade-12":"12 Deu MJ 12.1","teacher":""},

            ];


            doc.autoTable(columns, rows, {
                theme: 'striped',
                margin: {top: 60},
                beforePageContent: function(data) {
                    doc.text("Header", 40, 30);
                }
            });


            var pdf = doc.output('dataurlnewwindow'); //returns raw body of resulting PDF returned as a string as per the plugin documentation.

//            var data = new FormData();
//            data.append("data" , pdf);
//
//
//            $.ajax({
//                url: url,
//                data: data,
//                type: 'POST',
//                // THIS MUST BE DONE FOR FILE UPLOADING
//                contentType: false,
//                processData: false,
//                success: function(data)
//                    {
//                       console.log(data);
//                    }
//            });
