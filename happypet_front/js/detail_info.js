   // window.addEventListener('DOMContentLoaded',function(){

    window.onload = ()=>{
        function showMsg(msg){
            $('#myModal').modal('show')
            $('#alertMsg').text(msg)
        }

    let pdSeries = document.querySelector('[name="pdSeries"]')
    // let seriesInputs = document.querySelectorAll('input.seriesNum')
    // --------------點選後readonly -------start---------------------------
    // let checkBtn = document.querySelectorAll('.checkBtn')
    // let updateBtn = document.querySelectorAll('.updateBtn')
    // newItem.addEventListener('click',function(event){
    //     console.log('event.target = ',event.target)
    //     // 匹配特定選擇器且離當前元素最近的祖先元素（也可以是當前元素本身）
    //     let pdRow = event.target.closest('.pd_row')
    //     console.log('pdRow',pdRow)
        
    //     if(event.target.classList.contains('checkBtn')){
    //         let inputs = pdRow.querySelectorAll('input')
    //         inputs.forEach(function(input){
    //             input.name !== 'fullID[]' ?  input.setAttribute('readonly', true) : "";
    //         })
    //         console.log('hi')
    //     }else if(event.target.classList.contains('updateBtn')){
    //         // alert(123)
    //         let inputs = pdRow.querySelectorAll('input')
    //         inputs.forEach(function(input){
    //             input.name !== 'fullID[]' ?  input.removeAttribute('readonly') : "";
    //         })
    //     }
    // })
    // --------------點選後readonly -------end---------------------------

 

    $('[name="pdSeries"]').on('change',function(event){
        event.preventDefault();
        // console.log('我是產品系列的this',this.value)
       fetchData();
       
    })
    // 查詢系列號的系列名
    function fetchData(){
        // fetch('detail.php',{
        // let formData = new FormData(document.getElementById('myPd'));
        // formData.append('action', 'fetch');
        // fetch('insertDetail.php',{
        let pdSeries = document.querySelector('[name="pdSeries"]').value;
        fetch('http://localhost/happypet/happypet_back/public/api/product_back/detail/show',{
            method:'post',
            headers:{
                'Content-Type': 'application/json'
            },
            body:JSON.stringify({pdSeries})

        })
        .then(response=>{
            // console.log(response)
            // return response.text()   //圖片
            return response.json()  //陣列
        })
        .then(dataObj=>{
            console.log(dataObj)
            // ds202407001
            // 插入系列名
            // dataObj.name === undefined ? showMsg('查無此商品')  : pdName.value = obj.name

            if(!dataObj.series_name){
                showMsg(dataObj.error)
                pdName.value = ''
                $('input.fullPdID').each(function(index,fullInput){
                // fullPdIDInputs.forEach(fullInput => {
                    fullInput.value = ''
                });
            }else{
                pdName.value = dataObj.series_name
            }
            
        })
        .catch(error => {
            showMsg(error.error)
        });
    }
  

    // ---------------------------------------------------
    // 取得系列號與user輸入值，串接後放入fullID
    function getNum(){
        let pdIDInputs = document.querySelectorAll('input.pdID')
        let fullPdIDInputs = document.querySelectorAll('input.fullPdID')

        pdIDInputs.forEach(function(pdIDInput,index) {
            pdIDInput.addEventListener('input', function(){
            
            console.log('我是pdSeries.value',pdSeries.value)
                // pdSeries.value.trim() === undefined ? fullPdIDInputs[index].value = '' : fullPdIDInputs[index].value
                // pdSeries.value != undefined ? fullPdIDInputs[index].value = pdSeries.value.trim() + pdIDInput.value.trim() : fullPdIDInputs[index].value = ''
                fullPdIDInputs[index].value = pdSeries.value.trim() + pdIDInput.value.trim()

            })
        });
    }
    getNum()
    // ---------------------------------------------------

    let refreshBtn = document.querySelector('.refreshBtn')
    refreshBtn.onclick = ()=>{
        let inputs = document.querySelectorAll('input')    

        inputs.forEach((input)=>{
            input.value = ""
        })
    }



    // 綁定父元素
    newItem.addEventListener('click',function(event){
        let deleteBtn = event.target.closest('.deleteBtn')
        event.preventDefault()
        // 匹配特定選擇器且離當前元素最近的祖先元素（也可以是當前元素本身）
        // let pdRow = event.target.closest('.pd_row')
        let pdRow = deleteBtn.closest('.pd_row')
        // console.log('pdRow',pdRow)
        // console.log('event.target = ',event.target)
        pdRow.remove()
      
    })
    addBtn.addEventListener("click",(event)=>{
        console.log('我是新增欄')
        event.preventDefault();

        document.getElementById("newItem").insertAdjacentHTML("beforeend",
            `
            <tr class="pd_row">
                <td class="pd_id_area position-relative"> 
                    <button class="position-absolute deleteBtn"><i class="bi bi-x"></i></button>
                    <input class="pdID" type="text" form="myPd">
                    <input type="text" name="fullID[]" class="fullPdID" form="myPd" readonly>
                </td>
                <td><input type="text" name="flavor[]" form="myPd"></td>
                <td><input type="text" name="weight[]" form="myPd"></td>
                <td><input type="text" name="size[]" form="myPd"></td>
                <td><input type="text" name="color[]" form="myPd"></td>
                <td><input type="text" name="price[]" form="myPd"></td>
                <td><input type="text" name="GTIN[]" form="myPd"></td>
            </tr>
            `
        ) 
        // document.querySelector('.seriesNum').value = obj.id;
        getNum()
    })
    
    // document.getElementById('insertBtn').onclick = (event)=>{
    $('#insertBtn').click((event)=>{
        event.preventDefault();
        let formData = new FormData(document.getElementById('myPd'));
        // formData.append('action', 'insert');

        // 產品insert至資料庫
        // fetch('insertDetail.php',{
        fetch('http://localhost/happypet/happypet_back/public/api/product_back/detail/create',{
            method:'post',
            body:formData
            // body:new FormData(myPd)
        })
        .then(response=>{
            // return response.text()  
            console.log('我是點選insert後的response',response)
            console.log('response的content-type',response.headers.get('content-type'))
            if (!response.ok) {
                throw new Error(`錯誤：${response.statusText}`);
            }
            return response.json()  
            // return response.text()  
        })
        .then(data=>{
            console.log('data',data)
            if (data.message) {
                console.log(data.message);
                showMsg(data.message)

                setTimeout(()=>{
                    location.reload()// 刷新頁面
                },1000)
            } else if (data.error) {
                console.log('Error:', data.error);
                // alert(data.error)
                showMsg(data.error)
            }
        })
        .catch(error => {
            // console.error('Error:', error);
            showMsg(error)
            
        });
    })

    // fetchBtn.onclick = ()=>{
    //     fetch('fetchDetail.php',{
    //         method:'post',
           
    //     }).then(response=>{ 
    //         return response.json()  
    //     })
    //     .then(data=>{
    //         console.log('我是查詢出來的',data)
    //     })
    // }
    

}
// })