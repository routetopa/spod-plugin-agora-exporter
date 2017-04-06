AGORAEXPORTER = {};


AGORAEXPORTER.getPrettyDate = function(timestamp)
{
    var date = new Date(timestamp*1000);
    var monthNames = ["January", "February", "March","April", "May", "June", "July","August", "September", "October","November", "December"];
    var day = date.getDate();
    var monthIndex = date.getMonth();
    var year = date.getFullYear();
    var hours = date.getHours();
    var minutes = date.getMinutes();
    return (day + ' ' + monthNames[monthIndex] + ' ' + year + ' ' + hours + ':' + minutes);
};

AGORAEXPORTER.init = function()
{
    var room = JSON.parse(AGORAEXPORTER.commentsGraph);
    var elem = $("#container");
    var roomTopic = AGORAEXPORTER.body;

    elem.append("<h2>"+roomTopic+"</h2>");
    elem.append("<ol class='rounded-list' id='root'></ol>");
    elem = $("#root");

    room.forEach(function(node)
    {
        var sentimentClass = node.sentiment != "null" ? "sentiment_" + node.sentiment : "";
        elem.append("<li>" +
            "<a class='"+sentimentClass+"'>"+ node.username + " : <strong>" + node.comment + "</strong> -> " + node.timestamp + "</a>" +
            "<div id='datalet_" + node.id + "'></div>" +
            "<ol id='root_" + node.id + "'></ol>" +
            "</li>");
        var n_e = $("#root_"+node.id);

        node.children.forEach(function(children)
        {
            n_e.append("<li id=''>" +
                "<a class='"+sentimentClass+"'>"+ children.username + " : <strong>" + children.comment + "</strong> -> " + children.timestamp + "</a>" +
                "<div id='datalet_" + children.id + "'></div>" +
                "</li>");

            //DATALET
            if(children.component)
            {
                var params = JSON.parse(children.params);
                var fields = children.fields.split('","');

                for(var i=0; i<fields.length; i++)
                    fields[i] = fields[i].replace('"', '');

                ODE.loadDatalet(children.component, params, fields, '', "datalet_" + children.id);
            }

        });


        //DATALET
        if(node.component)
        {
            var params = JSON.parse(node.params);
            var fields = node.fields.split('","');

            for(var i=0; i<fields.length; i++)
                fields[i] = fields[i].replace('"', '');

            ODE.loadDatalet(node.component, params, fields, '', "datalet_" + node.id);
        }
    });
};

